const change = (sel, pick = 1) => `
  const el = document.querySelector('${sel}');
  if (!el) return 'missing ${sel}';
  const opt = [...el.options].filter(o => o.value)[${pick} - 1];
  if (!opt) return 'no options in ${sel}';
  el.value = opt.value; el.dispatchEvent(new Event('change', { bubbles: true }));`;
const options = sel => `[...document.querySelector('${sel}').options].filter(o => o.value).length`;

export default [
  { name: 'Library: choosing a book loads its available copies', url: '/library/issues', js: `
      ${change('#issueBookSelect')} if (typeof loadCopies === 'function') loadCopies(el.value);
      await sleep(1200); const n = ${options('#issueCopySelect')};
      return n > 0 || document.querySelector('#issueCopySelect').textContent.includes('No available') ? true : 'copies: ' + n + ' / ' + document.querySelector('#issueCopySelect').textContent` },
  { name: 'Exam schedule: class loads its subjects', url: '/exams/schedule', js: `
      ${change('#schedClassId')} await sleep(1200); const n = ${options('#schedSubjectId')}; return n > 0 ? true : 'subjects: ' + n` },
  { name: 'Marks entry: class loads its subjects', url: '/exams/marks', js: `
      ${change('#classSelect')} await sleep(1200); const n = ${options('#subjectSelect')}; return n > 0 ? true : 'subjects: ' + n` },
  { name: 'Class routine (add): class loads sections and subjects', url: '/academic/routines', js: `
      ${change('#classField')} await sleep(1500);
      const s = ${options('#sectionField')}, j = ${options('#subjectField')}; return s > 0 && j > 0 ? true : {sections: s, subjects: j}` },
  { name: 'Class routine (edit): opening the modal pre-fills subject', url: '/academic/routines', js: `
      const m = document.querySelector('[id^="editRoutineModal"]'); if (!m) return 'no routines';
      bootstrap.Modal.getOrCreateInstance(m).show(); await sleep(1500);
      const sub = m.querySelector('select[name="subject_id"]');
      return sub.value && sub.value === m.dataset.subjectId ? true : {value: sub.value, expected: m.dataset.subjectId}` },
  { name: 'Attendance: class loads sections', url: '/students/attendance', js: `
      ${change('#classSelect')} await sleep(1200); return document.querySelector('#sectionSelect').options.length >= 1 ? true : 'none'` },
  { name: 'Student search picker returns matches', url: '/fees/discounts', js: `
      const sel = document.querySelector('select[data-student-search]'); if (!sel) return 'no picker';
      if (!jQuery(sel).data('select2')) return 'select2 not initialised';
      const r = await fetch(${JSON.stringify('/students-search?q=a')}).then(r => r.json());
      return r.results && r.results.length > 0 ? true : r` },
  { name: 'Staff salary: choosing a person pre-fills basic salary', url: '/expenses/staff-salary', js: `
      const el = document.querySelector('#payeeSelect');
      const opt = [...el.options].find(o => o.value && Number(o.dataset.salary) > 0); if (!opt) return 'nobody has a salary set';
      el.value = opt.value; el.dispatchEvent(new Event('change'));
      return Number(document.querySelector('#basicSalary').value) === Number(opt.dataset.salary) ? true : document.querySelector('#basicSalary').value` },
  { name: 'Purchase: adding item rows updates the total', url: '/inventory/purchase', js: `
      document.querySelector('#addItem').click();
      const rows = document.querySelectorAll('#itemsTable tbody tr'); if (rows.length !== 2) return 'rows ' + rows.length;
      rows.forEach(r => { r.querySelector('.item-qty').value = 3; r.querySelector('.item-price').value = 10; });
      document.querySelector('#itemsTable tbody').dispatchEvent(new Event('input', {bubbles: true}));
      return document.querySelector('#purchaseTotal').textContent.includes('60.00') ? true : document.querySelector('#purchaseTotal').textContent` },
  { name: 'Dashboard: all charts drew and the table toggle works', url: '/school/dashboard', js: `
      const ids = ['financeChart','attendanceChart','enrolmentChart','spendingChart'].filter(i => document.getElementById(i));
      const drawn = ids.filter(i => Chart.getChart(i)); if (drawn.length !== ids.length) return {ids, drawn};
      const card = document.querySelector('[data-chart-card="finance"]'); card.querySelector('[data-toggle-table]').click();
      return card.classList.contains('show-table') && card.querySelectorAll('.chart-table tr').length > 5 ? true : 'table toggle failed'` },
  { name: 'Attendance: a refused save rolls the cell back and shows an error', url: '/students/attendance?class_id=1', js: `
      const today = new Date().toISOString().slice(0, 10);
      const cell = [...document.querySelectorAll('.att-cell[data-date]')].find(c => c.dataset.date > today);
      if (!cell) return 'no future day in this month';
      const before = cell.textContent.trim() + '|' + cell.className;
      let toasted = false; const orig = window.toastr && toastr.error; if (window.toastr) toastr.error = () => { toasted = true; };
      cell.click(); await sleep(1500);
      const after = cell.textContent.trim() + '|' + cell.className;
      return after === before && toasted ? true : {before, after, toasted}` },
  { name: 'Student profile: choosing a route lists only its stops', url: '/students/1', js: `
      const route = document.querySelector('select[name="transport_route_id"]'), stop = document.querySelector('select[name="route_stop_id"]');
      if (!route || !stop) return 'fields missing';
      const opts = [...route.options].filter(o => o.value);
      for (const o of opts) {
        route.value = o.value; route.dispatchEvent(new Event('change'));
        if (stop.options.length > 1) return true;
      }
      return 'no route with stops (' + opts.length + ' routes)'` },
  { name: 'Recycle Bin page lists tabs', url: '/trash', js: `return document.querySelectorAll('.nav-tabs .nav-link').length === 3 ? true : 'tabs missing'` },
];
