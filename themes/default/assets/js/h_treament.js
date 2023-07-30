$(document).ready(function () {
	$('body a, body button').attr('tabindex', -1);
	check_add_item_val();
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('tmreference')) {
                        localStorage.removeItem('tmreference');
                    }
					if (localStorage.getItem('tmnote')) {
                        localStorage.removeItem('tmnote');
                    }
					if (localStorage.getItem('tmnote')) {
                        localStorage.removeItem('tmnote');
                    }
                    
                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

    $('#tmreference').change(function (e) {
        localStorage.setItem('tmreference', $(this).val());
    });
    if (tmreference = localStorage.getItem('tmreference')) {
        $('#tmreference').val(tmreference);
    }
    $('#tmnote').redactor('destroy');
    $('#tmnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('tmnote', v);
        }
    });
    if (tmnote = localStorage.getItem('tmnote')) {
        $('#tmnote').redactor('set', tmnote);
    }
	
	var $patient = $('#tmpatient');
		$patient.change(function (e) {
        localStorage.setItem('tmpatient', $(this).val());
    });
    if (tmpatient = localStorage.getItem('tmpatient')) {
        $patient.val(tmpatient).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"hospitals/getPatient/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "hospitals/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    } else {
        nsPatient();
    }
	
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
    });
});


function nsPatient() {
    $('#tmpatient').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "hospitals/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}
