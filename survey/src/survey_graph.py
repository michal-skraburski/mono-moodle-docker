from __future__ import annotations

import argparse
import csv
import sys
from collections import Counter
from pathlib import Path

import matplotlib.pyplot as plt

SURVEY_IN = "survey-18-07-26.csv" # name of the survey file local
HEADER_ROWS = 3          # rows before the first real response
ROLE_COL = 18            # "teacher or a student" -- the 18th column (0-indexed)

QUESTIONS = [
    {
        "key": "Q1",
        "col": 19,
        "title": "Q1: Alternative side-by-side layout",
        "scale": [
            ("I don't like it.", "Dislike"),
            ("I like it.", "Like"),
            ("I like it, and would like to see it implemented on other "
             "question types.", "Like, extend it"),
        ],
    },
    {
        "key": "Q3",
        "col": 21,
        "title": "Q3: Question info-hide function",
        "scale": [
            ("I don't like it.", "Dislike"),
            ("It could be a good idea if improved.", "Mixed"),
            ("I like it.", "Like"),
        ],
    },
    {
        "key": "Q5",
        "col": 23,
        "title": "Q5: 'Save Draft' button",
        "scale": [
            ("I don't like it.", "Dislike"),
            ("It could be a good idea if improved.", "Mixed"),
            ("I like it.", "Like"),
        ],
    },
]

ROLE_LABELS = [
    ("Student", "Student"),
    ("Teacher", "Teacher"),
    ("I'm neither but would like to participate", "Neither"),
]

ROLE_COLOURS = {
    "Student": "#4C72B0",
    "Teacher": "#DD8452",
    "Neither": "#8C8C8C",
}


def load_rows(csv_path: Path) -> list[list[str]]:
    with open(csv_path, newline="", encoding="utf-8-sig") as f:
        rows = list(csv.reader(f))
    if len(rows) <= HEADER_ROWS:
        sys.exit(f"error: {csv_path} has no data rows below the headers")
    return rows[HEADER_ROWS:]


def role_of(raw: str) -> str | None:
    raw = raw.strip()
    for value, label in ROLE_LABELS:
        if raw == value:
            return label
    return None  # blank or unrecognised: excluded from the breakdown


def tally(rows, question) -> tuple[dict[str, Counter], Counter]:
    label_of = {raw: short for raw, short in question["scale"]}
    counts = {label: Counter() for _, label in ROLE_LABELS}
    answered = Counter()
    for row in rows:
        role = role_of(row[ROLE_COL])
        if role is None:
            continue
        answer = row[question["col"]].strip()
        if not answer:
            continue  # weird anomaly, should be present
        short = label_of.get(answer)
        if short is None:
            print(f"  warning: unmapped {question['key']} answer {answer!r}",
                  file=sys.stderr)
            continue
        counts[role][short] += 1
        answered[role] += 1
    return counts, answered


def plot_question(ax, question, counts, answered, normalize: bool) -> None:
    short_labels = [short for _, short in question["scale"]]
    roles = [label for _, label in ROLE_LABELS if answered[label] > 0]

    x = range(len(short_labels))
    n_roles = len(roles)
    width = 0.8 / max(n_roles, 1)

    for i, role in enumerate(roles):
        offset = (i - (n_roles - 1) / 2) * width
        if normalize:
            total = answered[role] or 1
            values = [100 * counts[role][s] / total for s in short_labels]
        else:
            values = [counts[role][s] for s in short_labels]
        bars = ax.bar([xi + offset for xi in x], values, width,
                      label=f"{role} (n={answered[role]})",
                      color=ROLE_COLOURS.get(role, "#555555"))
        ax.bar_label(bars, fmt="%.0f%%" if normalize else "%d",
                     padding=2, fontsize=8)

    ax.set_title(question["title"], fontsize=11)
    ax.set_xticks(list(x))
    ax.set_xticklabels(short_labels)
    ax.set_ylabel("Share of responses (%)" if normalize else "Responses")
    ax.spines[["top", "right"]].set_visible(False)
    ax.legend(fontsize=8, frameon=False)
    ax.margins(y=0.15)


def main() -> None:
    here = Path(__file__).resolve().parent
    default_csv = here.parent / SURVEY_IN
    default_out = here.parent / "out"

    parser = argparse.ArgumentParser(description=__doc__,
                                     formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--csv", type=Path, default=default_csv,
                        help=f"survey CSV (default: {default_csv.name})")
    parser.add_argument("--out", type=Path, default=default_out,
                        help="output directory for PNGs (default: survey/out)")
    parser.add_argument("--normalize", action="store_true",
                        help="show percentage within each role, not raw counts")
    args = parser.parse_args()

    args.out.mkdir(parents=True, exist_ok=True)
    rows = load_rows(args.csv)
    print(f"loaded {len(rows)} responses from {args.csv}")

    # One combined figure with a panel per question.
    fig, axes = plt.subplots(1, len(QUESTIONS),
                             figsize=(5 * len(QUESTIONS), 4.2))
    if len(QUESTIONS) == 1:
        axes = [axes]

    for ax, question in zip(axes, QUESTIONS):
        counts, answered = tally(rows, question)
        summary = ", ".join(f"{r}={dict(c)}" for r, c in counts.items()
                            if answered[r] > 0)
        print(f"  {question['key']}: {summary}")
        plot_question(ax, question, counts, answered, args.normalize)

        # Also emit a standalone figure for each question.
        one_fig, one_ax = plt.subplots(figsize=(5.2, 4.2))
        plot_question(one_ax, question, counts, answered, args.normalize)
        one_fig.tight_layout()
        single = args.out / f"survey_{question['key'].lower()}.png"
        one_fig.savefig(single, dpi=150)
        plt.close(one_fig)
        print(f"    wrote {single}")

    fig.suptitle("CodeRunner survey responses by respondent type", fontsize=13)
    fig.tight_layout(rect=(0, 0, 1, 0.96))
    combined = args.out / "survey_combined.png"
    fig.savefig(combined, dpi=150)
    plt.close(fig)
    print(f"  wrote {combined}")


if __name__ == "__main__":
    main()
