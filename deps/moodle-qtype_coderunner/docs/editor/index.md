# Coderunner Question Editor

## Table of Contents
1. [Question Type](#question-type)
   1. [Question type selector](#question-type-selector)
   2. [Customise & Show source](#customise--show-source)
   3. [Answer box lines](#answer-box-lines)
   4. [Submit buttons](#submit-buttons)
   5. [Give up](#give-up)
   6. [Feedback](#feedback)
   7. [Marking](#marking)
   8. [Template parameters](#template-parameters)
   9. [Twig controls](#twig-controls)
   10. [UI parameters](#ui-parameters)
2. [Customisation](#customisation)
   1. [Template](#template)
   2. [Template controls](#template-controls)
   3. [Grading](#grading)
   4. [Result columns](#result-columns)
   5. [UI controls](#ui-controls)
3. [Answer](#answer)
   1. [Sample answer](#sample-answer)
   2. [Answer preload](#answer-preload)
   3. [Global extra](#global-extra)
4. [Test Cases](#test-cases)
   1. [Test code](#test-code)
   2. [Standard input](#standard-input)
   3. [Expected output](#expected-output)
   4. [Extra](#extra)
   5. [Test case controls](#test-case-controls)
   6. [Test type](#test-type)
5. [Support files](#support-files)
6. [Attachment options](#attachment-options)
7. [Advanced customisation](#advanced-customisation)
   1. [Prototype](#prototype)
   2. [Sandbox controls](#sandbox-controls)
   3. [Languages](#languages)


---

## Question Type

### Question type selector

<div id="split-view">

<p>Select the question type from the dropdown. This determines which prototype the question inherits from — the prototype defines the language, sandbox, and default template. This field is required before the form can be saved.</p>

<img src="docs.php?page=/images/editorusage_questiontype.jpg" alt="Coderunner question type selector"/>

</div>

---

### Customise & Show source

<div id="split-view">

<div>
<p><strong>Customise</strong> — enables overriding fields inherited from the prototype (template, grader, sandbox settings, etc.). When unchecked the question uses the prototype exactly as defined. Checking this reveals the Customisation panel below.</p>
<p><strong>Show source</strong> — displays the template source to students alongside their answer. Mainly useful for debugging.</p>
</div>

<img src="docs.php?page=/images/editorusage_customise.jpg" alt="Coderunner customise checkboxes"/>

</div>

---

### Answer box lines

Controls the height of the student's answer box in lines. Default is 18. Increase for questions that expect longer programs, decrease for single-expression questions.

---

### Submit buttons

Controls the **Precheck** button shown to students:

| Option | Behaviour |
|---|---|
| Disabled | No precheck button shown |
| Empty | Precheck runs against an empty response |
| Examples | Precheck runs only test cases marked as examples |
| Selected | Precheck runs test cases whose Test type includes precheck |
| All | Precheck runs all test cases |

**Hide check** — hides the main Check button entirely. Use this when you want students to only be able to precheck before a final submission.

---

### Give up

Controls whether a **Stop and read feedback** button is shown to students:

| Option | Behaviour |
|---|---|
| Never | Button never shown |
| After max marks | Button shown once the student has achieved the maximum possible marks |
| Always | Button always shown |

---

### Feedback

Controls when test result feedback is shown to students:

| Option | Behaviour |
|---|---|
| Use quiz setting | Defers to the quiz-level feedback setting |
| Show | Always show feedback after submission |
| Hide | Never show feedback after submission |

---

### Marking

**All or nothing** — when checked, the student receives full marks only if all test cases pass; any failure gives zero. Per-test-case mark weights are disabled when this is on.

**Penalty regime** — a comma-separated list of penalty percentages applied to successive wrong submissions, e.g. `10, 20, ...`. The `...` suffix means the last two values define a linear progression that continues indefinitely.

---

### Template parameters

A JSON object (optionally written in Twig) that defines variables available to the template and test cases. Used to parameterise questions — for example to randomise inputs or share constants across test cases.

```json
{"max_value": 100, "language": "python3"}
```

---

### Twig controls

| Control | Description |
|---|---|
| Hoist template params | Makes template parameter keys available as top-level Twig variables |
| Extract code from JSON | Extracts student code from a JSON response (used with some UI plugins) |
| Twig all | Runs Twig expansion over all question fields, not just the template |
| Template params language | Language used to evaluate template params (`None`, `Twig`, `Python3`, etc.) |
| Evaluate per try | Re-evaluates template params on every student attempt (enables per-attempt randomisation) |

---

### UI parameters

A JSON object of parameters passed to the selected UI plugin. Valid keys depend on the plugin chosen. For example, for the Ace editor:

```json
{"theme": "monokai", "font_size": 14}
```

---

## Customisation

Visible only when **Customise** is checked.

### Template

The Twig/Jinja2 template that wraps the student's answer to produce an executable program. The template receives the student answer as `{{ STUDENT_ANSWER }}` and each test case's fields (`{{ TEST.testcode }}`, `{{ TEST.stdin }}`, etc.).

---

### Template controls

**Is combinator template** — when checked, the template runs once with all test cases combined rather than once per test case. Requires a **Test splitter regex** to separate results.

**Allow multiple stdins** — allows different `stdin` values per test case in combinator mode.

**Test splitter regex** — a regex used to split the combined output of a combinator template back into per-test-case results. Only active when combinator mode is on.

---

### Grading

| Grader | Behaviour |
|---|---|
| EqualityGrader | Passes if output exactly matches expected (trailing whitespace ignored) |
| NearEqualityGrader | Passes if output matches expected after normalising whitespace |
| RegexGrader | Passes if output matches expected interpreted as a regular expression |
| TemplateGrader | Template returns a grade and optional feedback rather than just output |

---

### Result columns

A JSON array controlling which columns appear in the test results table and how they are labelled. Each entry is a two-element array of `["column_name", "Header label"]`. Leave empty to use the defaults.

```json
[["testcode", "Code"], ["expected", "Expected"], ["got", "Got"]]
```

---

### UI controls

**Student answer UI** — the interface shown to students for entering their answer:

| Plugin | Description |
|---|---|
| ace | Syntax-highlighted code editor (default) |
| scratchpad | Ace editor with in-browser run button |
| table | Spreadsheet-style grid |
| graph | Visual graph/node editor |
| html | Fully custom HTML interface |

**Use ace** — enables the Ace editor for code fields within the question editor itself (not the student UI).

---

## Answer

### Sample answer

A correct answer to the question. If provided and **Validate on save** is checked, this answer is run against all test cases when the question is saved — the save fails if any test case fails. Also used as the answer when the quiz's "Fill in correct answers" feature is used.

---

### Answer preload

Text pre-filled into the student's answer box when the question first loads. Useful for providing a function signature, import statements, or a partial solution.

---

### Global extra

Extra text available to the template as `{{ QUESTION.globalextra }}`. Applies globally across all test cases. Useful for shared setup code or data that the template needs but that isn't part of any individual test case.

---

## Test Cases

Each test case defines one run of the student's code. Tests are executed in **ordering** sequence.

### Test code

Code passed to the template as `{{ TEST.testcode }}`. In a standard (non-combinator) template this typically means calling or invoking the student's function with specific inputs.

---

### Standard input

Text fed to the program's stdin during execution. Leave empty if the program does not read from stdin.

---

### Expected output

The output the program must produce to pass this test case. Compared against actual output by the selected grader.

---

### Extra

Additional data passed to the template as `{{ TEST.extra }}`. Only meaningful when using a TemplateGrader — ignored by the standard graders. Use it to pass expected return values, tolerances, or other grader-specific data.

---

### Test case controls

| Control | Description |
|---|---|
| Use as example | Marks this test as a visible example shown to students before they submit |
| Display | `SHOW` / `HIDE` / `HIDE_IF_FAIL` / `HIDE_IF_SUCCEED` — controls whether this test row appears in the results table |
| Hide rest if fail | If this test fails, all subsequent test rows are hidden from the student |
| Mark | Per-test weight used when **All or nothing** is off |
| Ordering | Execution order; lower numbers run first. Defaults to 10, 20, 30… |

---

### Test type

Controls which submission button triggers this test case:

| Type | Triggered by |
|---|---|
| Normal | Check only |
| Precheck | Precheck only |
| Both | Check and Precheck |

---

## Support files

Files uploaded here are copied into the sandbox working directory before each test run. Use for data files, helper modules, or any resource the student's code needs to `import` or `open`.

---

## Attachment options

Allows students to upload files alongside their code submission.

| Field | Description |
|---|---|
| Allow attachments | Maximum number of files a student may attach (0 = none, -1 = unlimited) |
| Attachments required | Minimum number of attachments the student must provide |
| Allowed filenames (regex) | A regex the uploaded filenames must match, e.g. `.*\.py` |
| Filename explanation | Plain-language explanation of the filename rule shown to students |
| Max file size | Maximum size per uploaded file |

---

## Advanced customisation

### Prototype

**Is prototype** — marks this question as a reusable prototype that other questions can inherit from. Set to `Yes (user defined)` and enter a **Type name** to make it available in the question type selector.

The type name must be unique within the course context and cannot duplicate a built-in prototype name.

---

### Sandbox controls

Overrides the sandbox execution environment for this question. Leave blank to inherit from the prototype.

| Field | Description |
|---|---|
| Sandbox | Which sandbox to use (`DEFAULT` uses the site-wide setting) |
| CPU time limit (secs) | Maximum CPU time per test run |
| Memory limit (MB) | Maximum RAM per test run |
| Sandbox params | JSON object of sandbox-specific parameters |

---

### Languages

**Language** — the sandbox language identifier (e.g. `python3`, `c`, `java`). Required when creating a prototype; inherited from the prototype otherwise.

**Ace language** — the language used for syntax highlighting in the Ace editor. Can differ from the sandbox language, e.g. set to `sql` while the sandbox language is `python3` (which runs the SQL via a Python wrapper). Accepts a comma-separated list for multilanguage questions; prefix the default with `*` (e.g. `python3, *java`).
