<?php

namespace App\Mail;

use App\Models\Parcel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParcelDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Parcel $parcel)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre colis a été récupéré avec succès — Buca Voyages VIP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.parcel_delivered',
        );
    }
}
