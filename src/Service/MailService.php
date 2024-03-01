<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendCredentials(string $email, string $prenom, string $password): void
    {
        $message = (new Email())
            ->from('noreply@citylunch.fr')
            ->to($email)
            ->subject('Bienvenue chez CityLunch — Vos identifiants')
            ->html("
                <h2>Bonjour {$prenom},</h2>
                <p>Votre compte livreur CityLunch a été créé.</p>
                <p><strong>Email :</strong> {$email}</p>
                <p><strong>Mot de passe :</strong> {$password}</p>
                <p>Connectez-vous via <code>POST /api/v1/auth/login</code> pour récupérer votre token JWT.</p>
                <p>L'équipe CityLunch</p>
            ");

        $this->mailer->send($message);
    }
}
