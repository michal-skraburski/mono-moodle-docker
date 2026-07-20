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
 * Unit tests for the example-question import page (import_example.php).
 * @group qtype_coderunner
 *
 * @package    qtype
 * @subpackage coderunner
 * @copyright  2012 Richard Lobb, University of Canterbury
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_coderunner;

use core\exception\moodle_exception;

defined("MOODLE_INTERNAL") || die();

class import_example_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * import_example.php is a plain script, like docs.php, so we fake the
     * request via the superglobals (read by required_param/optional_param)
     * and capture whatever it echoes with output buffering.
     * @param array $get contents for $_GET.
     * @param array $post contents for $_POST.
     * @return string the captured page output.
     */
    private function run_import_script(array $get, array $post = []): string
    {
        global $CFG;
        $_GET = $get;
        $_POST = $post;
        ob_start();
        try {
            include $CFG->dirroot . "/question/type/coderunner/import_example.php";
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Make a course plus a user enrolled in it with the given role.
     * @return array [course, user]
     */
    private function make_course_and_user(string $role): array
    {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(["fullname" => "Import Test Course"]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, $role);
        return [$course, $user];
    }

    public function test_course_chooser_lists_editable_courses()
    {
        [$course, $teacher] = $this->make_course_and_user("editingteacher");
        $this->setUser($teacher);
        $output = $this->run_import_script(["slug" => "01-hello-world"]);
        $this->assertStringContainsString("Import example: 01 hello world", $output);
        $this->assertStringContainsString($course->fullname, $output);
    }

    public function test_course_chooser_warns_without_editable_courses()
    {
        // Enrolled as student only: no course offers moodle/question:add.
        [, $student] = $this->make_course_and_user("student");
        $this->setUser($student);
        $output = $this->run_import_script(["slug" => "01-hello-world"]);
        $this->assertStringContainsString("not able to add questions to any course", $output);
    }

    public function test_import_creates_questions_in_course_category()
    {
        global $DB;
        [$course, $teacher] = $this->make_course_and_user("editingteacher");
        $this->setUser($teacher);
        try {
            $this->run_import_script(["slug" => "01-hello-world"], [
                "qtype_coderunner_import_courseid" => $course->id,
                "sesskey" => sesskey(),
            ]);
            $this->fail("Expected the success redirect to the question bank");
        } catch (moodle_exception $e) {
            // redirect() throws in CLI scripts rather than redirecting.
            $this->assertSame("redirecterrordetected", $e->errorcode);
        }
        $coursecontext = \context_course::instance($course->id);
        $category = $DB->get_record(
            "question_categories",
            ["contextid" => $coursecontext->id, "name" => "CodeRunner examples"]
        );
        $this->assertNotFalse($category, "Expected a 'CodeRunner examples' category");
        $this->assertTrue(
            $DB->record_exists("question", ["name" => "Hello, World!"]),
            "Expected the example question to have been imported"
        );
    }

    public function test_import_denied_without_capability()
    {
        // A student in the course must not be able to import even with a
        // valid sesskey and course id (e.g. a hand-crafted POST).
        [$course, $student] = $this->make_course_and_user("student");
        $this->setUser($student);
        $this->expectException(\required_capability_exception::class);
        $this->run_import_script(["slug" => "01-hello-world"], [
            "qtype_coderunner_import_courseid" => $course->id,
            "sesskey" => sesskey(),
        ]);
    }

    public function test_import_denied_without_sesskey()
    {
        [$course, $teacher] = $this->make_course_and_user("editingteacher");
        $this->setUser($teacher);
        try {
            $this->run_import_script(["slug" => "01-hello-world"], [
                "qtype_coderunner_import_courseid" => $course->id,
            ]);
            $this->fail("Expected a sesskey failure");
        } catch (moodle_exception $e) {
            $this->assertSame("invalidsesskey", $e->errorcode);
        }
    }

    public function test_bad_slug_rejected()
    {
        [, $teacher] = $this->make_course_and_user("editingteacher");
        $this->setUser($teacher);
        $this->expectException(moodle_exception::class);
        $this->run_import_script(["slug" => "does-not-exist"]);
    }
}
