<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailAtivarConta extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    private $nome;
    private $email;
    private $id_gerado;
    private $token;

    public function __construct($nome, $email, $id, $token)
    {
        $this->nome = $nome;
        $this->email = $email;
        $this->id_gerado = $id;
        $this->token = $token;        
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ative Sua Conta: Falta Apenas Um Clique!')

            ->greeting('Seja bem-vindo a Plataforma Maser Log!')

            ->line('Recebemos uma solicitação de ativação de conta.')

            ->line('Informações para login.')

            ->line('Nome: ' . $this->nome . '<br>
                    E-mail: ' . $this->email . '<br>
                    Senha: ' . 'maser' . $this->id_gerado)

            ->line('Para sua segurança, precisamos que você ative sua conta. Se não foi você ou alguém da sua empresa que solicitou o cadastro... apenas ignore este e-mail.')

            ->action('Ativar Minha Conta', \URL::action(
                'ApiUsuarioController@AtivarContaUsuario',
                ['email' => $this->email, 'token' => $this->token]
            ))

            ->line('Até a próxima!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
