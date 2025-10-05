@extends('layouts.app')

@section('content')
<style id="HOTFIX-FOR-INVISIBLE-FIELDS">/* --- TEMP hotfix: force controls to be visible --- */main form input, main form select, main form textarea {  padding: .5rem .75rem; min-height: 38px; box-sizing: border-box;}main form .form-group, main form .grid > * { min-width: 0; }/* date placeholders */input[type=date]::-webkit-datetime-edit { color: inherit; }</style>
<div class="max-w-6xl mx-auto">
  <h1 class="text-2xl font-semibold mb-6">New Admission</h1>

  <form class="admission-form" method="POST" action="{{ route('students.next') }}" id="admission-form">
    @csrf

    {{-- GUARDIAN / CONTACT --}}
    <div class="bg-white rounded-lg border p-5 mb-6">
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm text-gray-700">Guardian Name*</label>
          <input name="guardian_name" value="{{ old('guardian_name') }}" class="w-full mt-1 rounded-lg border-gray-300" required>
        </div>
        <div>
          <label class="text-sm text-gray-700">Phone</label>
          <input name="guardian_phone" value="{{ old('guardian_phone') }}" class="w-full mt-1 rounded-lg border-gray-300">
        </div>
        <div>
          <label class="text-sm text-gray-700">Email</label>
          <input name="guardian_email" value="{{ old('guardian_email') }}" class="w-full mt-1 rounded-lg border-gray-300">
        </div>
      </div>

      <div class="mt-4">
        <label class="text-sm text-gray-700">Address</label>
        <input name="address" value="{{ old('address') }}" class="w-full mt-1 rounded-lg border-gray-300">
      </div>

      <div class="grid md:grid-cols-4 gap-4 mt-4">
        <div class="md:col-span-2">
          <label class="text-sm text-gray-700">City</label>
          <input name="city" value="{{ old('city') }}" class="w-full mt-1 rounded-lg border-gray-300">
        </div>
        <div>
          <label class="text-sm text-gray-700">Post code</label>
          <input name="post_code" maxlength="10" value="{{ old('post_code') }}" class="w-full mt-1 rounded-lg border-gray-300" placeholder="e.g. E1 6AN">
        </div>
      </div>

      <div class="mt-4">
        <label class="text-sm text-gray-700">Notes</label>
        <textarea name="notes" rows="3" class="w-full mt-1 rounded-lg border-gray-300">{{ old('notes') }}</textarea>
      </div>
    </div>

    {{-- STUDENTS --}}
    <div class="bg-white rounded-lg border p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Students</h2>
        <button type="button" id="add-sibling-btn" class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-sm">+ Add sibling</button>
      </div>

      <div id="students-container" class="space-y-6">
        {{-- Initial Student 1 --}}
        <div class="student-row rounded-lg border p-4">
          <div class="text-sm font-medium text-gray-600 mb-3">Student <span class="student-number">1</span></div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm text-gray-700">First Name*</label>
              <input name="students[0][first_name]" class="w-full mt-1 rounded-lg border-gray-300" required>
            </div>
            <div>
              <label class="text-sm text-gray-700">Last Name</label>
              <input name="students[0][last_name]" class="w-full mt-1 rounded-lg border-gray-300">
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4 mt-3">
            <div>
              <label class="text-sm text-gray-700">DOB</label>
              <input name="students[0][dob]" placeholder="dd/mm/yyyy" class="w-full mt-1 rounded-lg border-gray-300">
            </div>
            <div>
              <label class="text-sm text-gray-700">Enroll Date</label>
              <input name="students[0][enroll_date]" placeholder="dd/mm/yyyy" class="w-full mt-1 rounded-lg border-gray-300">
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4 mt-3">
            <div>
              <label class="text-sm text-gray-700">Gender</label>
              <select name="students[0][gender]" class="w-full mt-1 rounded-lg border-gray-300">
                <option value="-">-</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-gray-700">Status</label>
              <select name="students[0][status]" class="w-full mt-1 rounded-lg border-gray-300">
                <option value="Active" selected>Active</option>
                <option value="Paused">Paused</option>
                <option value="Left">Left</option>
              </select>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4 mt-3">
            <div>
              <label class="text-sm text-gray-700">Fee Amount (£)</label>
              <input name="students[0][fee_amount]" class="w-full mt-1 rounded-lg border-gray-300">
            </div>
            <div>
              <label class="text-sm text-gray-700">Start</label>
              <input name="students[0][start]" placeholder="dd/mm/yyyy" class="w-full mt-1 rounded-lg border-gray-300">
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4 mt-3">
            <div>
              <label class="text-sm text-gray-700">Period</label>
              <select name="students[0][period]" class="w-full mt-1 rounded-lg border-gray-300">
                <option value="Monthly" selected>Monthly</option>
                <option value="Term">Term</option>
                <option value="Weekly">Weekly</option>
              </select>
            </div>
            <div class="flex items-center mt-6">
              <input type="checkbox" name="students[0][full_time]" value="1" class="mr-2">
              <span class="text-sm text-gray-700">Full-time</span>
            </div>
          </div>

          {{-- Remove button (only shown for clones) --}}
          <div class="mt-3 hidden remove-wrap">
            <button type="button" class="remove-student text-red-600 text-sm">Remove</button>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 flex justify-end">
      <button class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Next</button>
    </div>

  </form>
</div>

{{-- Simple JS to clone rows --}}
<script>
(function(){
  var box = document.getElementById('students-container');
  var addBtn = document.getElementById('add-sibling-btn');

  function reindex(){
    var rows = box.querySelectorAll('.student-row');
    rows.forEach(function(row, idx){
      row.querySelector('.student-number').textContent = (idx+1);
      // rename inputs to students[idx][field]
      row.querySelectorAll('input,select,textarea').forEach(function(el){
        var name = el.getAttribute('name');
        if(!name) return;
        name = name.replace(/students\[\d+\]/, 'students['+idx+']');
        el.setAttribute('name', name);
      });
      // show remove for all but first
      var rw = row.querySelector('.remove-wrap');
      if (rw) rw.classList.toggle('hidden', idx === 0);
    });
  }

  function makeClone(){
    var first = box.querySelector('.student-row');
    var clone = first.cloneNode(true);

    // clear values
    clone.querySelectorAll('input').forEach(function(i){
      if (i.type === 'checkbox') { i.checked = false; return; }
      i.value = '';
    });
    clone.querySelectorAll('select').forEach(function(s){
      // keep default (Active / Monthly)
      if (s.name.match(/\[status\]/)) s.value = 'Active';
      if (s.name.match(/\[period\]/)) s.value = 'Monthly';
    });

    // enable remove
    var removeBtn = clone.querySelector('.remove-student');
    if (removeBtn) {
      removeBtn.addEventListener('click', function(){
        clone.remove();
        reindex();
      });
    }
    box.appendChild(clone);
    reindex();
  }

  // wire initial remove handler on template (for clones we add in makeClone)
  var firstRemove = box.querySelector('.student-row .remove-student');
  if (firstRemove) firstRemove.addEventListener('click', function(){});

  addBtn.addEventListener('click', makeClone);
})();
</script>
@endsection
<!-- ADMISSION-FIELD-VISIBILITY-HOTFIX (safe to remove later) -->
<style>
/* Highest specificity & last in the file + !important to beat framework CSS */
.admission-form input,
.admission-form select,
.admission-form textarea,
.admission-form .form-control{
  display:block !important;
  width:100% !important;
  opacity:1 !important;
  visibility:visible !important;
  pointer-events:auto !important;
  background:#ffffff !important;
  color:#111111 !important;
  min-height:38px !important;
  border:1px solid #d1d5db !important; /* Tailwind gray-300 */
  border-radius:.375rem !important;
  padding:.5rem .75rem !important;
  box-shadow:none !important;
}
.admission-form input::placeholder,
.admission-form textarea::placeholder{ color:#9ca3af !important; } /* gray-400 */
.admission-form label{ color:#111111 !important; opacity:1 !important; }
</style>
<!-- ADMISSION-CHECKBOX-LAYOUT-HOTFIX -->
<style>
/* Keep checkbox + label on one line and vertically centered */
.admission-form .form-check,
.admission-form .checkbox,
.admission-form .checkbox-row,
.admission-form .full-time,
.admission-form .ft-group{
  display:flex !important;
  align-items:center !important;
  gap:.5rem !important;
}

/* Make generic checkbox layout work even without wrapper classes */
.admission-form input[type="checkbox"]{
  width:16px !important; height:16px !important;
  margin:0 !important; padding:0 !important;
  vertical-align:middle !important;
  transform:none !important;
}
.admission-form input[type="checkbox"] + label,
.admission-form label input[type="checkbox"]{
  display:inline-flex !important;
  align-items:center !important;
}
.admission-form input[type="checkbox"] + label{
  margin-left:.4rem !important;
}
.admission-form label{
  margin:0 !important; line-height:1.25 !important;
}

/* If the field lives inside a grid column, prevent it from stretching awkwardly */
.admission-form .full-time,
.admission-form .ft-group,
.admission-form .checkbox-row{
  min-height:38px !important;
}

/* Tidy the little chevron from select next to it so it doesn't overlap */
.admission-form select{
  background-position: right .6rem center !important;
}
</style>
<style>/* FULLTIME_SPACING_FALLBACK */
  .student-row input[type=checkbox]{ margin-right:8px; vertical-align:middle; }
</style>
<style>/* FULLTIME_SPACING_FALLBACK_V2 */
  .student-row input[type=checkbox]{ margin-right:8px; vertical-align:middle; width:1rem; height:1rem; }
</style>
<style>/* FULLTIME_SPACING_FALLBACK_V3 */
  .student-row input[type=checkbox]{ margin-right:8px; vertical-align:middle; width:1rem; height:1rem; }
</style>
<script>/* DATE_ENFORCER_V3 */
(function(){
  function fixDates(root){
    var selectors = [
      'input[name$="[dob]"]','input[name*="[dob]"]','input[name="dob"]',
      'input[name$="[enroll_date]"]','input[name*="[enroll_date]"]','input[name="enroll_date"]',
      'input[name$="[start]"]','input[name*="[start]"]','input[name="start"]',
      'input[placeholder="dd/mm/yyyy"]'
    ];
    var q = selectors.join(',');
    var nodes = (root.querySelectorAll ? root.querySelectorAll(q) : []);
    nodes.forEach(function(el){
      try{
        if (el.type !== 'date') el.setAttribute('type','date');
        if (!el.hasAttribute('lang')) el.setAttribute('lang','en-GB');
        if (!el.placeholder) el.setAttribute('placeholder','dd/mm/yyyy');
        el.style.minWidth = '10rem';
      } catch(e){}
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    fixDates(document);
    var box = document.getElementById('students-container') || document;
    try{
      var mo = new MutationObserver(function(list){
        list.forEach(function(rec){
          rec.addedNodes.forEach(function(n){
            if (n && n.querySelectorAll) fixDates(n);
          });
        });
      });
      mo.observe(box, {childList:true, subtree:true});
    }catch(e){}
  });
})();
</script>
<style>/* FULLTIME_SPACING_GLOBAL */
  .student-row input[type=checkbox]{ margin-right:8px; vertical-align:middle; width:1rem; height:1rem; }
</style>
<script>// DATE_EN_GB_ENFORCER
(function(){
  function touchDates(root){
    var sels=[
      'input[name$="[dob]"]','input[name="dob"]',
      'input[name$="[enroll_date]"]','input[name="enroll_date"]',
      'input[name$="[start]"]','input[name="start"]'
    ];
    root.querySelectorAll(sels.join(',')).forEach(function(el){
      try{
        if(el.getAttribute('type')!=='date') el.setAttribute('type','date');
        el.setAttribute('lang','en-GB');
        if(!el.placeholder) el.setAttribute('placeholder','dd/mm/yyyy');
        el.style.minWidth='10rem';
      }catch(e){}
    });
  }
  document.addEventListener('DOMContentLoaded',function(){ touchDates(document); });
  new MutationObserver(function(muts){
    muts.forEach(function(m){
      m.addedNodes && m.addedNodes.forEach(function(n){
        if(n.querySelectorAll) touchDates(n);
      });
    });
  }).observe(document.documentElement,{childList:true,subtree:true});
})();
</script>
<style>/* FULLTIME_SPACING_FIX */
  .student-row input[type=checkbox]{margin-right:8px;vertical-align:middle;width:1rem;height:1rem;}
  .student-row label.inline-label{display:inline-flex;align-items:center;gap:.5rem;}
</style>
<script><!-- DATE_PICKER_FIX -->
(function(){
  function forceNativeDates(scope){
    var q = [
      'input[name$="[dob]"]','input[name="dob"]',
      'input[name$="[enroll_date]"]','input[name="enroll_date"]',
      'input[name$="[start]"]','input[name="start"]',
      'input[placeholder="dd/mm/yyyy"]'
    ].join(',');
    (scope.querySelectorAll ? scope.querySelectorAll(q) : []).forEach(function(el){
      try{
        if(el.getAttribute('type') !== 'date') el.setAttribute('type','date');
        if(!el.getAttribute('lang')) el.setAttribute('lang','en-GB');  // helps mobile pickers
        if(!el.getAttribute('placeholder')) el.setAttribute('placeholder','dd/mm/yyyy');
        el.style.minWidth = '10rem';
      }catch(e){}
    });
  }
  document.addEventListener('DOMContentLoaded', function(){
    forceNativeDates(document);
    // If siblings are added dynamically, try again after clicks on common triggers
    document.body.addEventListener('click', function(e){
      var t = e.target;
      if(!t) return;
      if(t.matches('#add-sibling-btn,[data-add-sibling],button, a')) { setTimeout(function(){ forceNativeDates(document); }, 200); }
    }, true);
  });
})();
</script>
<script id="DATE-FORCER">
(function(){
  function fixDates(root){
    root.querySelectorAll('input[placeholder="dd/mm/yyyy"]').forEach(function(el){
      try{
        if(el.type !== 'date') el.setAttribute('type','date');
        el.setAttribute('lang','en-GB');
      }catch(e){}
    });
  }
  document.addEventListener('DOMContentLoaded', function(){ fixDates(document); });
})();
</script>
