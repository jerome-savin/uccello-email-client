<?php

namespace JeromeSavin\UccelloEmailClient\Widgets;

use Arrilot\Widgets\AbstractWidget;

class UserEmails extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        // Get module
        $module = ucmodule('mail-client');

        $emails = [];
        $columns = [];

        $column = new \StdClass;
        $column->name = 'subject';
        $column->label = 'Objet';
        $columns[] = $column;

        $column = new \StdClass;
        $column->name = 'from';
        $column->label = 'Expediteur';
        $columns[] = $column;

        $column = new \StdClass;
        $column->name = 'preview';
        $column->label = 'Contenu';
        $columns[] = $column;

        $column = new \StdClass;
        $column->name = 'date';
        $column->label = 'Date';
        $columns[] = $column;

        $domain = ucdomain($this->config['domain']);
        

        $emailController = new \JeromeSavin\UccelloEmailClient\Http\Controllers\MailClientController();
        $mails = $emailController->userEmail(10);
        
        if (is_array($mails)) {
            usort($mails, function ($a, $b) {
                return ($a->getReceivedDateTime() < $b->getReceivedDateTime());
            });

            foreach ($mails as $mail) {
                $email = new \StdClass;
                $email->type    = 'received';
                $email->subject = $mail->getSubject();
                $email->preview = substr($mail->getBodyPreview(), 0, 40);
                $email->from    =  substr($mail->getFrom()->getEmailAddress()->getaddress(), 0, 40);;
                $email->date    = \Carbon\Carbon::parse($mail->getReceivedDateTime())->timeZone(config('app.timezone', 'Europe/Paris'))->format("d/m/Y - H:i");
                $email->webLink = $mail->getWebLink();
                $emails[] = $email;
            }
        }
        return view('uccello-email-client::widgets.user_emails', [
            'config' => $this->config,
            'domain' => ucdomain($this->config['domain']),
            'module' => $module,
            'columns' => $columns,
            'emails' => $emails,
        ]);
    }
}
