import 'materialize-css'

function loadMails(folder, account)
{
    $('#calendar-loader').css('visibility', 'visible')
    $.ajax({
        type:'POST',
        url: '/ginkgo/o365/mails/'+account+'/'+folder,
        dataType: "html",
        async: true,
        cache: false,
        success: function(response)
        {
            $('#mail-container').html(response);
            $('.tooltipped').tooltip();
            $('#calendar-loader').css('visibility', 'hidden')
        }
    }); 
}

$( document ).ready(function() {
    let folder = 'inbox';
    let account = $('meta[name="first_account_id"]').attr('content')
    console.log(account);
    console.log(folder);

    $.ajaxSetup({
        headers: {
          'X-CSRF-Token': $('meta[name="_token"]').attr('content')
        }
      });

      $(".folder").on('click', function(event){
        folder = $(this).data('folder-id')
        account = $(this).data('account-id')
        loadMails(folder, account)
    });
    loadMails(folder, account);
    window.setInterval(function(){
        loadMails(folder);
    }, 60000);
});