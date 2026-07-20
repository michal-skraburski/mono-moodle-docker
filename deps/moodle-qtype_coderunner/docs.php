<?php

// This file is part of CodeRunner - http://coderunner.org.nz/
//
// CodeRunner is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// CodeRunner is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with CodeRunner.  If not, see <http://www.gnu.org/licenses/>.

/**
 * coderunner question definition classes.
 * @author     Michal Skraburski
 * @package    qtype_coderunner
 * @copyright  Richard Lobb, 2011, The University of Canterbury
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
use core\exception\moodle_exception;

// Explicit globals so this still works when included from inside a function
// scope (e.g. a PHPUnit test), not just when run as the top-level script.
global $PAGE, $OUTPUT, $CFG;

require_once($CFG->libdir . '/markdown/MarkdownInterface.php');
require_once($CFG->libdir . '/markdown/Markdown.php');
require_once($CFG->libdir . '/markdown/MarkdownExtra.php');

$docsdir = __DIR__ . '/docs/editor/';
$page = optional_param('page', 'index.md', PARAM_PATH);
$file = realpath($docsdir . '/' . $page);


// The second half of this condition checks whether
// the $file is under $docsdir
// realpath() will return false if the file doesn't exist.
if (!$file || strpos($file, realpath($docsdir)) !== 0) {
    $file = false;
    throw new moodle_exception('invalidparameter', 'error');
}

// This catches any JPEGs, may be due for removal
$ext = pathinfo($file, PATHINFO_EXTENSION);
if ($ext == 'jpg') {
    header('Content-Type:' . 'image/jpeg');
    readfile($file);
    // Top-level return ends the script just like exit, but also lets the
    // PHPUnit tests include this file without terminating the test run.
    return;
}

// Serve example question exports (Moodle XML) as downloads rather than
// rendering them, so authors can import them into their question bank.
if ($ext == 'xml') {
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    readfile($file);
    return;
}

$pages = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docsdir)) as $f) {
    if ($f->getExtension() === 'md') {
        $slug = ltrim(str_replace($docsdir, '', $f->getPathname()), '/');
        $slug = substr($slug, 0, -3); // strip .md
        $pages[] = $slug;
    }
}
sort($pages);

$PAGE->set_url('/question/type/coderunner/docs.php', ['page' => $page]);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('CodeRunner Documentation');

// inject CSS as \@imports do not work on ./styles.css
$PAGE->requires->css(new moodle_url('/question/type/coderunner/docs/editor/styles/docs.css'));

// This block of code fixes the lack of id tags attached to headers.

$md = new \Michelf\MarkdownExtra();
$md->header_id_func = function ($headervalue) {
    $slug = strtolower(trim($headervalue));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
};
// End fix

echo $OUTPUT->header();
$contents = file_get_contents($file);

// Any page may include an auto-generated list of the example question
// exports in example_questions/ by placing this marker in its markdown.
// Dropping a new .xml file into that directory is all that's needed for
// it to appear — no page edits required.
$listmarker = '<!-- EXAMPLE_QUESTIONS_LIST -->';
if (strpos($contents, $listmarker) !== false) {
    $examplefiles = glob($docsdir . '/example_questions/*.xml') ?: [];
    sort($examplefiles);
    if ($examplefiles) {
        $list = '';
        foreach ($examplefiles as $examplefile) {
            $filename = basename($examplefile);
            $slug = substr($filename, 0, -4);
            $title = ucfirst(str_replace(['-', '_'], ' ', $slug));
            // Encode the filename: raw spaces (or parentheses) in the URL
            // stop Markdown from recognising the link at all.
            $list .= "- [$title](import_example.php?slug=" . rawurlencode($slug) . ")"
                . "   -   [download](docs.php?page=example_questions/" . rawurlencode($filename) . ")\n";
        }
    } else {
        $list = '*No example questions have been added yet.*';
    }
    $contents = str_replace($listmarker, $list, $contents);
}

// MarkdownExtra's doExtraAttributes() calls preg_match_all() on a null $attr
// whenever a heading has no explicit {#id}/{.class} block, which is the
// normal case now that header_id_func always supplies a default id. That's
// a harmless no-op there, but PHP 8.1+ still emits a deprecation notice for
// it, so silence just that during the transform.
// TODO: maybe do a pull request on that
$previouserrorlevel = error_reporting(E_ALL & ~E_DEPRECATED);
echo $md->transform($contents);
error_reporting($previouserrorlevel);
echo $OUTPUT->footer();
