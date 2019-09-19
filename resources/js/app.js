import 'materialize-css'

export class MailClient {
    constructor() {
        this.modal = $('#addEventModal')
        this.initMailRowListener()
    }

    initMailRowListener()
    {
        $(".email-row").on('click', (event) => {
            $(event.currentTarget).closest('tr').next('tr').toggle('show');
            let mailId = $(event.currentTarget).data('mailid');
            
            //$(this.modal).modal('open')
        })
    }
}

new MailClient()// Your JavaScript code here