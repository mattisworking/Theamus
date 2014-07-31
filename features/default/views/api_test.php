<div id='api-result'></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        Theamus.Ajax.api({
            type:   'get',
            url:    Theamus.base_url+'/accounts/',
            method: ['Accounts', 'test'],
            success:function(data) {
                console.log(data);

                if (data.error.status === 1) {
                    $('#api-result').html(alert_notify('danger', data.error.message));
                } else {
                    $('#api-result').html(alert_notify('success', data.response.data));
                }
            }
        });
    });
</script>