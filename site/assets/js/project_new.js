import $ from 'jquery';
import 'bootstrap-select';

import '../scss/project_new.scss';

// propose a name from the displayName
// The name does not follow all validation rules for simplicity, i.e. it's
// possible for a proposed name to fail validation.
$('#form_displayName').keyup(function () {
    var nodeName = $('#form_name');
    var proposedName = $(this).val().replace(/[\s_]/g, '-').replace(/[^a-z0-9-]/ig, '').toLowerCase();
    nodeName.val(proposedName);
});
