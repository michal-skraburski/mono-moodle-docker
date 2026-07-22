---
title: What to explore
---
### Back to [index](index)

# Explore page
**Warning:** this page expects you to be on the `new` sub path at [moodle.skraburski.com/new](https://moodle.skraburski.com/new).

## Login
It is highly recommended to start by logging in [https://moodle.skraburski.com/new/login/index.php](https://moodle.skraburski.com/new/login/index.php).
At certain features you'll be prompted to login, you can find login details on the email/download page.

## Documentation page
Starting with the documentation page at [https://moodle.skraburski.com/new/question/type/coderunner/docs.php?page=index.md](https://moodle.skraburski.com/new/question/type/coderunner/docs.php?page=index.md), you can find a large documentation page. The entirety of this document are
changes I implemented. 

### Question Examples
The interesting part of this documentation page are question examples, you can find this on the page
here [https://moodle.skraburski.com/new/question/type/coderunner/docs.php?page=example_questions.md](https://moodle.skraburski.com/new/question/type/coderunner/docs.php?page=example_questions.md).  
On this page you can find a list of questions which you can either download, or if you are logged in -- import.
This feature lets you import examples directly into the courses you've got access to. This is a part of
the 'learning by examples' requirement inferred by the survey study.

#### Question Walkthroughs
The next page [example walkthroughs](https://moodle.skraburski.com/new/question/type/coderunner/docs.php?page=example_questions.md),
allows you to view a select subset of the questions in the examples set in full detail. 5 questions
were selected to be explained with greater detail, with each option laid out.

## Author page 
The fastest way to get to the author page is to [create a question](https://moodle.skraburski.com/new/question/bank/editquestion/question.php?qtype=coderunner&category=4&courseid=2&returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D]).
This page has a few differences:
- The test cases divs are rounded.
- You can delete testcases.
- Precheck div is now aligned correctly with the corresponding test case.
  - Enable `selected` on precheck, then scroll to the test cases.
- Lighthouse score is 97% on `new` and 93% on `old`.

## Student page
The fastest way to get to the student page is to preview a question. 
You can find a question bank here [https://moodle.skraburski.com/new/question/edit.php?courseid=2&cat=7%2C11](https://moodle.skraburski.com/new/question/edit.php?courseid=2&cat=7%2C11).
Click on **Edit** > **Preview**. This will allow you to preview the question selected, here you can also
click **Fill in correct responses**.

Main differences:
- Question info hide functionality
  - Side effect: Layout margin reduction
- Layout switching, you can toggle between stacked and side by side

Ideally, you should access a Quiz which is essentially a list of questions, this will allow you to see how
layout switching looks mixed with other question types. 
