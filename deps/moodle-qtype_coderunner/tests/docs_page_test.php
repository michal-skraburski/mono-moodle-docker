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
 * Unit tests for coderunner docs page.
 * @group qtype_coderunner
 * Assumed to be run after python questions have been tested, so focuses
 * only on C-specific aspects.
 *
 * @package    qtype
 * @subpackage coderunner
 * @copyright  2012 Richard Lobb, University of Canterbury
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_coderunner;

use core\exception\moodle_exception;
use PHPUnit\Framework\Testcase;

defined("MOODLE_INTERNAL") || die();

global $CFG;

class docs_page_test extends Testcase
{
    /**
     * docs.php is a plain script, not a function, so there's nothing to call
     * directly. Instead we fake the request (via $_GET, read by optional_param)
     * and capture whatever it echoes with output buffering.
     * @param string $page value of the 'page' request parameter.
     * @return string the captured page output.
     */
    private function render_docs_page(string $page): string
    {
        global $CFG;
        $_GET["page"] = $page;
        ob_start();
        try {
            include $CFG->dirroot . "/question/type/coderunner/docs.php";
            return ob_get_clean();
        } catch (\Throwable $e) {
            // docs.php throws before reaching the ob_get_clean() above, so the
            // buffer would otherwise stay open when this propagates out.
            ob_end_clean();
            throw $e;
        }
    }

    public function test_valid_page()
    {
        $output = $this->render_docs_page("index.md");
        $this->assertStringContainsString("Coderunner Question Editor", $output);
    }

    public function test_outofbounds_page()
    {
        $this->expectException(moodle_exception::class);
        // Two directories above docs/editor/ is the plugin root: escapes docsdir
        // but still points at a real file, so this also can't be caught by a
        // simple file_exists() check alone.
        $output = $this->render_docs_page("../../version.php");
        ob_end_flush();
    }

    public function test_nonexistent_page()
    {
        $this->expectException(moodle_exception::class);
        $output = $this->render_docs_page("does-not-exist");
        ob_end_flush();
    }
}
