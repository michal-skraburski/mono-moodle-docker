// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

define([], function () {

  const STORAGE_KEY = 'coderunner_layout';

  /**
   * Inject the layout toggle controls and wire up handlers.
   * @param {string} questionId The id attribute of the .que.coderunner element.
   */
  function layoutSwitcher(questionId) {
    const que = document.getElementById(questionId);
    if (!que) {
      return;
    }

    const formulation = que.querySelector('.formulation');

    const questionBox = que.querySelector('.formulation .question_box');
    const answerBox = que.querySelector('.formulation .answer-box'); // TODO: fix this inconsistency

    const divider = que.querySelector('.formulation .divider');
    if (!divider || !answerBox || !questionBox || !formulation) {
      return;
    }

    let dragStartX = 0;
    let questionStartWidth = 0;
    let answerStartWidth = 0;

    const onDividerDrag = event => {
      // The complexity from this comes from needing to be able to dynamically
      // resize based on window size changing as well as the divider moving.
      const delta = event.clientX - dragStartX;
      const newQuestionWidth = questionStartWidth + delta;
      const newAnswerWidth = answerStartWidth - delta;
      const totalWidth = newQuestionWidth + newAnswerWidth;
      const questionRatio = newQuestionWidth / totalWidth;
      const answerRatio = newAnswerWidth / totalWidth;
      questionBox.style.flex = `${questionRatio} ${questionRatio} 0`;
      answerBox.style.flex = `${answerRatio} ${answerRatio} 0`;
    };

    const onDividerDragEnd = () => {
      document.removeEventListener('mousemove', onDividerDrag);
      document.removeEventListener('mouseup', onDividerDragEnd);
    };

    divider.addEventListener('mousedown', event => {
      event.preventDefault();
      dragStartX = event.clientX;
      questionStartWidth = questionBox.getBoundingClientRect().width;
      answerStartWidth = answerBox.getBoundingClientRect().width;
      document.addEventListener('mousemove', onDividerDrag);
      document.addEventListener('mouseup', onDividerDragEnd);
    });


    const controls = document.createElement('div');
    controls.className = 'coderunner-layout-controls';

    const splitBtn = document.createElement('button');
    splitBtn.className = 'coderunner-layout-btn';
    splitBtn.ariaLabel = 'Press this button to switch to a Side by Side view';
    splitBtn.type = 'button';
    splitBtn.title = 'Side by side';
    splitBtn.innerHTML = '&#x2b1c;&#x2b1c;';  // ⬜⬜

    const stackBtn = document.createElement('button');
    stackBtn.className = 'coderunner-layout-btn';
    stackBtn.ariaLabel = 'Press this button to switch to a vertically stacked view';
    stackBtn.type = 'button';
    stackBtn.title = 'Stacked';
    stackBtn.innerHTML = '&#x2b1c;';  // ⬜

    controls.appendChild(splitBtn);
    controls.appendChild(stackBtn);
    formulation.prepend(controls);

    /**
     * Apply the given layout mode to the question and persist it.
     * @param {string} mode Either 'split' or 'stacked'.
     */
    function applyLayout(mode) {
      // A prior drag may have pinned these to inline pixel widths, which would
      // otherwise permanently override the stylesheet's flex rules below.
      questionBox.style.flex = '';
      answerBox.style.flex = '';
      if (mode === 'stacked') {
        que.classList.add('layout-stacked');
        stackBtn.classList.add('active');
        splitBtn.classList.remove('active');
      } else {
        que.classList.remove('layout-stacked');
        splitBtn.classList.add('active');
        stackBtn.classList.remove('active');
      }
      try {
        localStorage.setItem(STORAGE_KEY, mode);
      } catch (e) { /* storage may be unavailable */ }
    }

    splitBtn.addEventListener('click', () => applyLayout('split'));
    stackBtn.addEventListener('click', () => applyLayout('stacked'));

    const saved = (() => {
      try { return localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
    })();
    applyLayout(saved === 'stacked' ? 'stacked' : 'split');
  }

  return { layoutSwitcher };
});
