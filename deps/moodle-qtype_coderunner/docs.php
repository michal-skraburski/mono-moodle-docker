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

require_once('../../../config.php');
use core\exception\moodle_exception;

$docsdir = __DIR__ . '/docs/editor/';
$page = optional_param('page', 'index', PARAM_PATH);
$file = realpath($docsdir . '/' . $page);


// The second half of this condition checks whether
// the $file is under $docsdir
// realpath() will return false if the file doesn't exist.
if (!$file || strpos($file, realpath($docsdir)) !== 0) {
  $file = false;
  throw new moodle_exception('invalidparameter', 'error');
}

$ext = pathinfo($file, PATHINFO_EXTENSION);
if ($ext == 'jpg') {
  header('Content-Type:' . 'image/jpeg');
  readfile($file);
  exit;
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

echo $OUTPUT->header();
// your content here
echo markdown_to_html(file_get_contents($file));
echo $OUTPUT->footer();
