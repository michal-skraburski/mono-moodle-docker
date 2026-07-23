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

/**
 * Live "auto-save" hint for the CodeRunner Save draft button.
 *
 * Reflects Moodle's own quiz autosave (M.mod_quiz.autosave) so the student can
 * see that their answer is saved automatically, when the next save is due, and
 * when a save happens. It only reveals itself inside a real quiz attempt where
 * autosave actually runs, so nothing is claimed in preview or other contexts.
 *
 * @module     qtype_coderunner/savedrafthint
 * @copyright  2026 Michal Skraburski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
  'use strict';

  // How long the "Saved just now" confirmation lingers before returning to idle.
  var SAVED_MESSAGE_MS = 4000;
  // Give the quiz's YUI autosave module time to load and initialise.
  var AUTOSAVE_WAIT_MS = 500;
  var AUTOSAVE_WAIT_TRIES = 10;
  var FALLBACK_DELAY_MS = 60000;

  /**
   * The live quiz autosave object, but only once it is actually running.
   * @return {Object|null} M.mod_quiz.autosave, or null outside a quiz attempt.
   */
  function getAutosave() {
    var m = window.M;
    if (m && m.mod_quiz && m.mod_quiz.autosave && m.mod_quiz.autosave.form) {
      return m.mod_quiz.autosave;
    }
    return null;
  }

  /**
   * Drive the live hint against a running autosave.
   * @param {HTMLElement} box the hint container.
   * @param {HTMLElement|null} button the Save draft button.
   * @param {Object} autosave the M.mod_quiz.autosave object.
   * @param {Object} strings the localised labels (idle, dirty, saving, saved, countdown).
   */
  function run(box, button, autosave, strings) {
    var label = box.querySelector('.cr-savedraft-label');
    var count = box.querySelector('.cr-savedraft-count');
    var delayMs = (typeof autosave.delay === 'number' && autosave.delay > 0) ? autosave.delay : FALLBACK_DELAY_MS;

    var dirtySince = null;
    var savedAt = 0;
    var prevDirty = false;
    var prevSaving = false;
    var lastLabel = null;

    box.hidden = false;
    if (button) {
      button.setAttribute('aria-describedby', box.id);
      button.title = strings.idle;
    }

    // Only touch the live label on a real state change, so a screen reader is
    // not re-notified every second by the ticking countdown (which is
    // aria-hidden in the markup).
    function setLabel(text) {
      if (text !== lastLabel) {
        label.textContent = text;
        lastLabel = text;
      }
    }

    function tick() {
      var saving = !!autosave.save_transaction;
      var dirty = !!autosave.dirty;

      if (dirty && !prevDirty) {
        dirtySince = Date.now();
      }
      if (!dirty && prevDirty) {
        dirtySince = null;
        savedAt = Date.now();
      }
      if (prevSaving && !saving) {
        savedAt = Date.now();
      }
      prevDirty = dirty;
      prevSaving = saving;

      if (saving) {
        setLabel(strings.saving);
        count.textContent = '';
      } else if (dirty) {
        setLabel(strings.dirty);
        var start = dirtySince || Date.now();
        var remain = Math.max(0, Math.ceil((start + delayMs - Date.now()) / 1000));
        count.textContent = ' · ' + strings.countdown.replace('{seconds}', remain);
      } else if (savedAt && Date.now() - savedAt < SAVED_MESSAGE_MS) {
        setLabel(strings.saved);
        count.textContent = '';
      } else {
        setLabel(strings.idle);
        count.textContent = '';
      }
    }

    tick();
    window.setInterval(tick, 1000);
  }

  /**
   * Wire up the hint, waiting briefly for the quiz autosave to initialise.
   * @param {String} hintId id of the hidden hint container.
   * @param {String} buttonId id of the Save draft button.
   * @param {Object} strings localised labels (idle, dirty, saving, saved, countdown).
   */
  function init(hintId, buttonId, strings) {
    var box = document.getElementById(hintId);
    if (!box) {
      return;
    }
    var button = buttonId ? document.getElementById(buttonId) : null;
    var tries = 0;

    function attempt() {
      var autosave = getAutosave();
      if (autosave) {
        run(box, button, autosave, strings);
        return;
      }
      if (tries++ < AUTOSAVE_WAIT_TRIES) {
        window.setTimeout(attempt, AUTOSAVE_WAIT_MS);
      }
      // Otherwise not a quiz attempt: leave the hint hidden and claim nothing.
    }

    attempt();
  }

  return {init: init};
});
