<?php

namespace JeromeSavin\UccelloEmailClient\Widgets;

use Arrilot\Widgets\AbstractWidget;

class EmailsRelated extends AbstractWidget
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
        $module = ucmodule($this->config['module']);

        // Get record
        $modelClass = $module->model_class;
        $record = $modelClass::find($this->config['record_id']);

        $emails = [];
        $columns = [];

        $field = $this->config['data']->field ?? 'email';

        if ($record->$field) {
            $search = $record->$field;

            if (strpos($search, '@')) {
                $column = new \StdClass;
                $column->name = 'type';
                $column->label = 'Type';
                $columns[] = $column;
            }

            $column = new \StdClass;
            $column->name = 'subject';
            $column->label = 'Objet';
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
            if (strpos($search, '@')) {
                $mails = $emailController->mailsFromTo($search);
            } else {
                $mails = $emailController->mailsKeyword($search);
            }
            
            if (is_array($mails)) {
                usort($mails, function ($a, $b) {
                    return ($a->getReceivedDateTime() < $b->getReceivedDateTime());
                });

                foreach ($mails as $mail) {
                    $email = new \StdClass;
                    $email->type    = $mail->getFrom()->getEmailAddress()->getaddress()==$search ? 'received' : 'sent';
                    $email->subject = $mail->getSubject();
                    $email->preview = $mail->getBodyPreview();
                    $email->date    = \Carbon\Carbon::parse($mail->getReceivedDateTime())->timeZone('Europe/Paris')->format("d/m/Y - H:i");
                    $email->webLink = $mail->getWebLink();
                    $emails[] = $email;
                }
            }
            return view('uccello-email-client::widgets.emails_related', [
                'config' => $this->config,
                'domain' => ucdomain($this->config['domain']),
                'module' => $module,
                'data' => (object) $this->config['data'],
                'record' => $record,
                'label' => $this->config['data']->label ?? $this->config['labelForTranslation'],
                'columns' => $columns,
                'emails' => $emails,
                'searchIsAdress' => strpos($search, '@')
            ]);
        }
        return view('uccello-email-client::widgets.emails_related', [
            'config' => $this->config,
            'domain' => ucdomain($this->config['domain']),
            'module' => $module,
            'data' => (object) $this->config['data'],
            'record' => $record,
            'label' => $this->config['data']->label ?? $this->config['labelForTranslation'],
            'columns' => $columns,
            'emails' => $emails,
            'searchIsAdress' => ''
        ]);
    }
}
