/* ── Modals ─────────────────────────────────────────────────── */
document.querySelectorAll('[data-modal-open]').forEach((button) => {
  button.addEventListener('click', () => {
    const modal = document.getElementById(button.dataset.modalOpen);
    if (modal) modal.classList.add('is-open');
  });
});

document.querySelectorAll('[data-modal-close]').forEach((button) => {
  button.addEventListener('click', () => {
    button.closest('.modal-backdrop')?.classList.remove('is-open');
  });
});

document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
  backdrop.addEventListener('click', (event) => {
    if (event.target === backdrop) backdrop.classList.remove('is-open');
  });
});

/* ── Sidebar user popover ────────────────────────────────────── */
const userBtn     = document.getElementById('sidebar-user-btn');
const userPopover = document.getElementById('user-popover');

if (userBtn && userPopover) {
  userBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = userPopover.classList.toggle('is-open');
    userBtn.classList.toggle('is-open', isOpen);
    userBtn.setAttribute('aria-expanded', String(isOpen));
  });

  document.addEventListener('click', (e) => {
    if (!userBtn.contains(e.target) && !userPopover.contains(e.target)) {
      userPopover.classList.remove('is-open');
      userBtn.classList.remove('is-open');
      userBtn.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      userPopover.classList.remove('is-open');
      userBtn.classList.remove('is-open');
      userBtn.setAttribute('aria-expanded', 'false');
    }
  });
}

/* ── Student picker cards (visual checkbox) ─────────────────── */
document.querySelectorAll('.student-pick-card').forEach((card) => {
  const checkbox = card.querySelector('input[type="checkbox"]');
  if (!checkbox) return;

  card.classList.toggle('is-checked', checkbox.checked);

  checkbox.addEventListener('change', () => {
    card.classList.toggle('is-checked', checkbox.checked);
  });
});

/* ── Exam wizard ─────────────────────────────────────────────── */
const examSubject          = document.getElementById('exam-subject');
const examUnit             = document.getElementById('exam-unit');
const questionPicks        = document.querySelectorAll('.question-pick');
const selectedQuestionCount= document.getElementById('selected-question-count');
const emptyQuestionFilter  = document.getElementById('empty-question-filter');
const examCount            = document.getElementById('exam-count');
const wizard               = document.getElementById('exam-wizard');
let wizardStep = 1;

function filterQuestionBank() {
  if (!examSubject || !examUnit || questionPicks.length === 0) return;
  const subject = examSubject.value;
  const unit    = examUnit.value;
  let visibleCount = 0;

  questionPicks.forEach((item) => {
    const matches = subject && unit && item.dataset.subject === subject && item.dataset.unit === unit;
    item.classList.toggle('is-visible', Boolean(matches));
    if (matches) visibleCount += 1;
    if (!matches) {
      const cb = item.querySelector('input[type="checkbox"]');
      if (cb) cb.checked = false;
    }
  });

  if (emptyQuestionFilter) {
    emptyQuestionFilter.textContent = subject && unit
      ? 'No hay preguntas registradas para esa materia y unidad.'
      : 'Selecciona materia y unidad en el paso 1 para ver las preguntas disponibles.';
    emptyQuestionFilter.hidden = visibleCount > 0;
  }
  updateSelectedQuestions();
}

examSubject?.addEventListener('change', filterQuestionBank);
examUnit?.addEventListener('change', filterQuestionBank);
filterQuestionBank();

function updateSelectedQuestions() {
  const selected = document.querySelectorAll('.question-pick.is-visible input[type="checkbox"]:checked').length;
  if (selectedQuestionCount) selectedQuestionCount.textContent = String(selected);
  if (examCount) {
    const currentValue = Number(examCount.value) || 1;
    examCount.innerHTML = '';
    const maxVal = selected > 0 ? selected : 1;
    for (let i = 1; i <= maxVal; i++) {
      const option = document.createElement('option');
      option.value = String(i);
      option.textContent = String(i);
      if (i === currentValue || (i === maxVal && currentValue > maxVal)) {
        option.selected = true;
      }
      examCount.appendChild(option);
    }
  }
}

questionPicks.forEach((item) => {
  item.querySelector('input[type="checkbox"]')?.addEventListener('change', updateSelectedQuestions);
});

function showWizardStep(step) {
  wizardStep = step;
  document.querySelectorAll('.wizard-step').forEach((section) => {
    section.classList.toggle('is-active', section.dataset.step === String(step));
  });
  document.querySelectorAll('[data-step-indicator]').forEach((indicator) => {
    indicator.classList.toggle('is-active', indicator.dataset.stepIndicator === String(step));
  });
}

function canMoveFromStep(step) {
  if (step === 1) {
    const title = wizard?.querySelector('input[name="title"]');
    return Boolean(title?.value.trim() && examSubject?.value && examUnit?.value);
  }
  if (step === 2) {
    return document.querySelectorAll('.question-pick.is-visible input[type="checkbox"]:checked').length > 0;
  }
  if (step === 3) {
    return document.querySelectorAll('.student-pick-card input[type="checkbox"]:checked').length > 0;
  }
  return true;
}

wizard?.querySelectorAll('[data-wizard-next]').forEach((button) => {
  button.addEventListener('click', () => {
    if (!canMoveFromStep(wizardStep)) {
      wizard.reportValidity();
      if (wizardStep === 2) alert('Selecciona al menos una pregunta para continuar.');
      if (wizardStep === 3) alert('Selecciona al menos un alumno para continuar.');
      return;
    }
    if (wizardStep === 1) filterQuestionBank();
    if (wizardStep === 2) updateSelectedQuestions();
    showWizardStep(Math.min(4, wizardStep + 1));
  });
});

wizard?.querySelectorAll('[data-wizard-prev]').forEach((button) => {
  button.addEventListener('click', () => showWizardStep(Math.max(1, wizardStep - 1)));
});

wizard?.addEventListener('submit', (event) => {
  updateSelectedQuestions();
  const selected = document.querySelectorAll('.question-pick.is-visible input[type="checkbox"]:checked').length;
  const studentsSelected = document.querySelectorAll('.student-pick-card input[type="checkbox"]:checked').length;
  if (selected === 0 || Number(examCount?.value || 0) > selected || studentsSelected === 0) {
    event.preventDefault();
    if (selected === 0) {
      showWizardStep(2);
    } else if (studentsSelected === 0) {
      showWizardStep(3);
    } else {
      showWizardStep(4);
    }
    alert('Revisa la seleccion de preguntas, alumnos y la cantidad solicitada.');
  }
});

showWizardStep(1);

/* ── Student Exam Drafts Autosave & Recovery ────────────────── */
// 1. Update Responder -> Continuar buttons on exams list
document.querySelectorAll('.exam-action-btn').forEach((btn) => {
  const examId = btn.dataset.examId;
  const draft = localStorage.getItem(`sigea_exam_draft_${examId}`);
  if (draft && draft !== '{}') {
    btn.textContent = 'Continuar';
  }
});

// Clear any drafts for exams that are completed
document.querySelectorAll('[data-clear-exam-id]').forEach((el) => {
  const examId = el.dataset.clearExamId;
  localStorage.removeItem(`sigea_exam_draft_${examId}`);
});

// 2. Draft Autosave/Restore on take_exam.php
const examSheetForm = document.querySelector('form.exam-sheet');
if (examSheetForm) {
  const urlParams = new URLSearchParams(window.location.search);
  const examId = urlParams.get('id');
  if (examId) {
    const storageKey = `sigea_exam_draft_${examId}`;
    
    // Restore from draft
    const savedDraft = localStorage.getItem(storageKey);
    if (savedDraft) {
      try {
        const draft = JSON.parse(savedDraft);
        Object.keys(draft).forEach((qId) => {
          const selectedIds = draft[qId];
          selectedIds.forEach((optId) => {
            const input = examSheetForm.querySelector(
              `input[name="answers[${qId}]"][value="${optId}"], input[name="answers[${qId}][]"][value="${optId}"]`
            );
            if (input) {
              input.checked = true;
            }
          });
        });
      } catch (e) {
        console.error("Error restoring exam draft", e);
      }
    }
    
    // Auto-save on change
    examSheetForm.addEventListener('change', () => {
      const draft = {};
      examSheetForm.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked').forEach((input) => {
        const match = input.name.match(/answers\[(\d+)\]/);
        if (match) {
          const qId = match[1];
          if (!draft[qId]) {
            draft[qId] = [];
          }
          draft[qId].push(input.value);
        }
      });
      if (Object.keys(draft).length > 0) {
        localStorage.setItem(storageKey, JSON.stringify(draft));
      } else {
        localStorage.removeItem(storageKey);
      }
    });
  }
}
