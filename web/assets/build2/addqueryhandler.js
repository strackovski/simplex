/**
 * Created by vstrackovski on 01/02/15.
 */

/*
 function querySelection() {
 var contentTypeField, columnField, operatorField, valueField, sortByField, limitMaxField;

 if ($('.editQuery-form').length) {
 $('.add-pq-form').attr("data-proto", '');
 $('.new-query-form').remove();
 //$('.edit-form').wrap('<div class="row"></div>').removeClass('edit-form').addClass('edit-form-group').find('label.control-label').first().hide();
 contentTypeField = $('#page_queries_0_contentType');
 columnField = $('#page_queries_0_column');
 operatorField = $('#page_queries_0_operator');
 valueField = $('#page_queries_0_value');
 sortByField = $('#page_queries_0_sortBy');
 limitMaxField = $('#page_queries_0_limitMax');
 } else {
 var forma = $('.add-pq-form').attr("data-proto");
 $('.add-pq-form').attr("data-proto", '');
 $('.tab-pane#data').prepend(forma);
 contentTypeField = $('#page_queries___name___contentType');
 columnField = $('#page_queries___name___column');
 operatorField = $('#page_queries___name___operator');
 valueField = $('#page_queries___name___value');
 sortByField = $('#page_queries___name___sortBy');
 limitMaxField = $('#page_queries___name___limitMax');
 }

 var authors = $('#page_authors').closest('.data-group');
 var tags = $('#page_tags').closest('.data-group');

 // move tags and authors in the correct field
 $('#page_authors').closest('.data-group').remove();
 authors.insertAfter(valueField.closest('.data-group'));

 $('#page_tags').closest('.data-group').remove();
 tags.insertAfter(valueField.closest('.data-group'));

 // hide authors & tags form
 //$('#page_authors').closest('.data-group').addClass('hidden');
 //$('#page_tags').closest('.data-group').addClass('hidden');
 authors.addClass('hidden');
 tags.addClass('hidden');

 var column = columnField.val();
 var value = valueField.val();

 // arrays to be inserted into valueField field
 var tagsCheckboxes = [];
 var authorsCheckboxes = [];
 var dates = [];

 // hide operator options, unhide them later, based on columnField selection
 operatorField.find('option').addClass('hidden');


 // if contentType is not defined, a new page is being created
 if (!columnField.val()) {
 //hide all secondary options, unhide first choices
 contentTypeField.closest('.data-group').nextAll().addClass('hidden');
 columnField.closest('.data-group').removeClass('hidden');
 operatorField.closest('.data-group').removeClass('hidden');
 operatorField.find('option[value="eq"]').removeClass('hidden').prop('selected', true);
 operatorField.find('option[value="in"]').removeClass('hidden');
 valueField.closest('.data-group').removeClass('hidden');
 limitMaxField.closest('.data-group').removeClass('hidden');
 sortByField.closest('.data-group').removeClass('hidden');
 //operatorField.find('option').removeClass('hidden');

 } else {
 if (column == 'title') {
 // show 'equals' and 'contains' options in operandField
 operatorField.find('option[value="eq"]').removeClass('hidden');
 operatorField.find('option[value="in"]').removeClass('hidden');

 } else if (column == 'created_at' || column == 'updated_at') {

 // hide valueField field
 valueField.closest('.data-group').addClass('hidden');

 // show 'after', 'before' and 'between' options in operandField
 operatorField.find('option[value="before"]').removeClass('hidden');
 operatorField.find('option[value="after"]').removeClass('hidden');
 operatorField.find('option[value="between"]').removeClass('hidden');

 // conjure up date fields, if they have not been created yet, apply datepicker() on them
 if (!($('.dateField').length)) {
 var valueFieldClone2 = valueField.closest('.data-group').clone().addClass('dateField toDateField').removeClass('hidden');
 $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone2.find('.form-element'));
 valueFieldClone2.find('.form-element').addClass('prepend-icon');
 // change label, id, remove 'required' attr
 valueFieldClone2.find('label').text('And date:').attr('for', 'date_2');
 valueFieldClone2.find('input').attr({id: 'date_2', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
 onClose: function (dateText) {
 // call a function that collects dateField values and inserts them into valueField field
 dateChange();
 }
 });
 valueFieldClone2.insertAfter(valueField.closest('.data-group'));

 // change label, id, remove 'required' attr, unhide forms
 var valueFieldClone1 = valueField.closest('.data-group').clone().addClass('dateField fromDateField').removeClass('hidden');
 $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone1.find('.form-element'));
 valueFieldClone1.find('.form-element').addClass('prepend-icon');
 valueFieldClone1.find('label').text('Date: ').attr('for', 'date_1');
 valueFieldClone1.find('input').attr({id: 'date_1', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
 onClose: function (dateText) {
 // call a function that collects dateField values and inserts them into valueField field
 dateChange();
 }
 });
 valueFieldClone1.insertAfter(valueField.closest('.data-group'));
 }

 // collect comma separated values from the valueField and insert them into correct dateFields and unhide them
 var datesArray = value.split(',');
 if (operatorField.val() == 'before' || operatorField.val() == 'after') {
 $('.fromDateField').find('input#date_1').val(datesArray[0]);
 $('.toDateField').addClass('hidden').find('input#date_2').val('');
 } else if (operatorField.val() == 'between') {
 $('.fromDateField').find('input#date_1').val(datesArray[0]);
 $('.toDateField').find('input#date_2').val(datesArray[1]);
 }
 } else if (column == 'author') {
 authorsAction('edit');
 } else if (column == 'tags') {
 tagsAction('edit');
 }

 }

 // Input changes
 contentTypeField.on('change', function () {
 // hide all sibling .form-groups
 //$(this).closest('.row').siblings().find('.data-group').addClass('hidden');
 //$(this).closest('.data-group').nextAll().addClass('hidden');

 // hide tags and authors // NEW
 //$('#page_tags').closest('.form-group').addClass('hidden');
 //$('#page_authors').closest('.form-group').addClass('hidden');

 // unhide columnField
 //$(this).closest('.data-group').next().removeClass('hidden');

 // set columnField to 'Select a column ...'
 columnField.find('option:eq(0)').prop('selected', true);
 operatorField.find('option').addClass('hidden');
 operatorField.find('option[value="eq"]').removeClass('hidden').prop('selected', true);
 operatorField.find('option[value="in"]').removeClass('hidden');
 valueField.val('');
 valueField.closest('.data-group').removeClass('hidden');
 tags.addClass('hidden');
 authors.addClass('hidden');
 $('.dateField').addClass('hidden');
 $('.dateField').val('');
 });

 columnField.on('change', function () {
 operatorField.find('option').addClass('hidden');
 valueField.val('');
 valueField.closest('.data-group').addClass('hidden');

 $('.dateField').val('');
 $('.dateField').closest('.data-group').addClass('hidden');

 authors.addClass('hidden');
 tags.addClass('hidden');

 tagsCheckboxes.length = 0;
 authorsCheckboxes.length = 0;
 dates.length = 0;

 var columnCurrent = $(this).val();

 if (columnCurrent == 'title') {
 valueField.closest('.data-group').removeClass('hidden');

 // operatorField, show 'equals' and 'contains' options
 operatorField.closest('.data-group').removeClass('hidden');
 operatorField.find('option[value="eq"]').removeClass('hidden').prop('selected', true);
 operatorField.find('option[value="in"]').removeClass('hidden');

 } else if (columnCurrent == 'updated_at' || columnCurrent == 'created_at') {

 // show operatorField, show 'after', 'before' and 'between' options, make 'between' the selected one
 //$('.dateField').removeClass('hidden');
 operatorField.closest('.data-group').removeClass('hidden');
 operatorField.find('option[value="before"]').removeClass('hidden');
 operatorField.find('option[value="after"]').removeClass('hidden');
 operatorField.find('option[value="between"]').removeClass('hidden').prop('selected', true);

 // conjure up date fields, if they have not been created yet, apply datepicker() on them
 if (!($('.dateField').length)) {
 var valueFieldClone2 = valueField.closest('.data-group').clone().addClass('dateField toDateField').removeClass('hidden');
 $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone2.find('.form-element'));
 valueFieldClone2.find('.form-element').addClass('prepend-icon');
 valueFieldClone2.find('label').text('And date: ').attr('for', 'date_2');
 valueFieldClone2.find('input').attr({id: 'date_2', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
 onClose: function (dateText) {
 dateChange();
 }
 });
 valueFieldClone2.insertAfter(valueField.closest('.data-group'));

 var valueFieldClone1 = valueField.closest('.data-group').clone().addClass('dateField fromDateField').removeClass('hidden');
 $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone1.find('.form-element'));
 valueFieldClone1.find('.form-element').addClass('prepend-icon');
 valueFieldClone1.find('label').text('Date:').attr('for', 'date_1');
 valueFieldClone1.find('input').attr({id: 'date_1', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
 onClose: function (dateText) {
 dateChange();
 }
 });
 valueFieldClone1.insertAfter(valueField.closest('.data-group'));
 } else { // just unhide dateFields
 $('.dateField').removeClass('hidden');
 }

 } else if (columnCurrent == 'author') {
 authorsAction();

 } else if (columnCurrent == 'tags') {
 tagsAction();
 }
 //limitMinField.closest('.data-group').removeClass('hidden');
 //limitMaxField.closest('.data-group').removeClass('hidden');
 //sortByField.closest('.data-group').removeClass('hidden');

 });

 operatorField.on('change', function () {
 dates.length = 0;
 var columnCurrent;
 if ($('#page_queries___name___column').length) {
 columnCurrent = $('#page_queries___name___column').val();
 } else {
 columnCurrent = $('#page_queries_0_column').val();
 }

 var currentOperator = $(this).val();
 if (columnCurrent == 'updated_at' || columnCurrent == 'created_at') {
 $('.dateField').addClass('hidden').find('input').val('');
 if (currentOperator == 'between') {
 valueField.val('');
 $('.dateField').removeClass('hidden');
 } else if (currentOperator == 'before' || currentOperator == 'after') {
 $('.fromDateField').removeClass('hidden');
 valueField.val('');
 }
 }
 });

 function dateChange() {
 dates.length = 0;
 var operatorCurrent;
 if ($('#page_queries___name___operator').length) {
 operatorCurrent = $('#page_queries___name___operator').val();
 } else {
 operatorCurrent = $('#page_queries_0_operator').val();
 }

 if (operatorCurrent == 'before' || operatorCurrent == 'after') {
 dates.push($('#date_1').val());
 } else if (operatorCurrent == 'between') {
 if (!($('#date_2').val())) {
 dates.push($('#date_1').val());
 } else if (!($('#date_1').val())) {
 dates.push($('#date_2').val());
 } else {
 dates.push($('#date_1').val());
 dates.push($('#date_2').val());
 }
 }
 var joinedString = dates.join(',');
 valueField.val(joinedString);
 }

 $('#page_tags > .checkbox').find('input[type="checkbox"]').off('change').on('change', function () {
 var joinedString;
 var $this = $(this);
 var $thisValue = $this.val();

 if ($this.is(':checked')) {
 // add to array
 if ($.inArray($thisValue, tagsCheckboxes)  == -1) {
 tagsCheckboxes.push($thisValue);
 joinedString = tagsCheckboxes.join(',');
 valueField.val(joinedString);
 }
 } else {
 // remove from array
 tagsCheckboxes = jQuery.grep(tagsCheckboxes, function (value) {
 return value != $thisValue;
 });

 joinedString = tagsCheckboxes.join(',');
 valueField.val(joinedString);
 }
 });

 $('#page_authors > .checkbox').find('input[type="checkbox"]').off('change').on('change', function () {
 var joinedString;
 var $this = $(this);
 var $thisValue = $this.val();

 if ($this.is(':checked')) {
 // add to array
 if ($.inArray($thisValue, authorsCheckboxes)  == -1) {
 authorsCheckboxes.push($thisValue);
 joinedString = authorsCheckboxes.join(',');
 valueField.val(joinedString);
 }
 } else {
 // remove from array
 authorsCheckboxes = jQuery.grep(authorsCheckboxes, function (value) {
 return value != $thisValue;
 });

 joinedString = authorsCheckboxes.join(',');
 valueField.val(joinedString);
 }
 });

 function authorsAction(edit) {
 // get author checkboxes on the page
 var pageAuthors = $('#page_authors > .checkbox').find('input[type="checkbox"]');

 // get author values from the valueField
 var authorsArray = [];
 if (edit) {
 if (value != '') {
 authorsArray = value.split(',');
 }
 }
 authorsCheckboxes = authorsArray;

 // trigger checkbox click if ids match - click triggers $(...).on('change'), handled later
 $.each(authorsArray, function (index, item) {
 $.each(pageAuthors, function () {
 if (item == $(this).val()) {
 $(this).trigger('click');
 }
 });
 });

 // show form, hide operatorField and valueField
 $('#page_authors').closest('.data-group').removeClass('hidden');
 operatorField.closest('.data-group').addClass('hidden');
 valueField.closest('.data-group').addClass('hidden');
 $('.dateField').closest('.data-group').addClass('hidden');
 }

 function tagsAction(edit) {
 var pageTags = $('#page_tags > .checkbox').find('input[type="checkbox"]');

 // get tag values from the valueField
 var tagsArray = [];
 if (edit) {
 if (value != '') {
 tagsArray = value.split(',');
 }
 }

 tagsCheckboxes = tagsArray;

 $.each(tagsArray, function (index, item) {
 $.each(pageTags, function () {
 if (item == $(this).val()) {
 $(this).trigger('click');
 }
 });
 });

 // show form, hide operatorField and valueField
 $('#page_tags').closest('.data-group').removeClass('hidden');
 operatorField.closest('.data-group').addClass('hidden');
 valueField.closest('.data-group').addClass('hidden');
 $('.dateField').closest('.data-group').addClass('hidden');
 }
 }
 */