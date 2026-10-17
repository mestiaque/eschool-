{{--
    Searchable student picker (select2 + AJAX), so pages don't load every student.
    @include('school-management::partials.student-select', ['name' => 'student_id', 'required' => true, 'placeholder' => 'Select Student'])
--}}
@php
    $fieldName   = $name ?? 'student_id';
    $selectedId  = old($fieldName, $selected ?? request($fieldName));
    $selectedObj = $selectedId ? \ME\SchoolManagement\Models\Student::with('classe')->find($selectedId) : null;
@endphp
<select name="{{ $fieldName }}" class="form-select form-select-sm {{ $class ?? '' }}" data-student-search
        data-placeholder="{{ $placeholder ?? 'Search student by name, ID or roll' }}" {{ !empty($required) ? 'required' : '' }} style="width:100%">
    <option value="">{{ $placeholder ?? 'Search student by name, ID or roll' }}</option>
    @if($selectedObj)
        <option value="{{ $selectedObj->id }}" selected>{{ $selectedObj->full_name }} ({{ $selectedObj->student_id }}) – {{ $selectedObj->classe?->name }}</option>
    @endif
</select>

@once
@push('scripts')
<script>
(function () {
    function init(root) {
        if (!window.jQuery || !jQuery.fn.select2) return;
        jQuery(root).find('select[data-student-search]').each(function () {
            const $el = jQuery(this);
            if ($el.data('select2')) return;
            const modal = $el.closest('.modal');
            $el.select2({
                width: '100%',
                allowClear: !$el.prop('required'),
                placeholder: $el.data('placeholder'),
                dropdownParent: modal.length ? modal : jQuery(document.body),
                minimumInputLength: 0,
                ajax: {
                    url: @json(route('students.search')),
                    dataType: 'json', delay: 250,
                    data: p => ({ q: p.term || '', page: p.page || 1 }),
                },
            });
        });
    }
    document.addEventListener('DOMContentLoaded', () => init(document));
    if (document.readyState !== 'loading') init(document);
})();
</script>
@endpush
@endonce
