# Stripe — configuració i proves (branch: feature/stripe-payment)

Aquest fitxer explica com provar la integració Stripe implementada a la branca feature/stripe-payment.

IMPORTANT: NO commitejar mai claus reals. Utilitzeu `.env.local` per a valors locals.

Variables d'entorn (exemple de `.env.local`)

STRIPE_SECRET_KEY=sk_test_XXXXXXXXXXXXXXXXXXXXXXXXX
STRIPE_PUBLISHABLE_KEY=pk_test_XXXXXXXXXXXXXXXXXXXX
STRIPE_WEBHOOK_SECRET=whsec_XXXXXXXXXXXXXXXXXXXXXXXXX
MAILER_DSN=smtp://user:pass@smtp.mailtrap.io:2525

Passos per provar localment

1. Crear `.env.local` amb les variables anteriors (adaptar valors de test).
2. Instal·lar dependències: `composer install`.
   - Nota: composer.lock del repo no conté stripe; si cal actualitzar localment: `composer update stripe/stripe-php --with-dependencies` (pot requerir ext-mbstring i/o ajustar la versió de PHP local).
3. Iniciar servidor Symfony: `symfony server:start`.
4. Obrir la pàgina de checkout: `http://localhost:8000/payment/checkout` i fer clic a "Pagar 20€".
5. Proves de targeta: utilitzar la targeta de test `4242 4242 4242 4242` amb qualsevol CVC i caducitat futura.
6. Escoltar webhooks (local): instal·lar Stripe CLI i executar:
   ```bash
   stripe listen --forward-to localhost:8000/payment/webhook
   ```
7. Verificar correus: configurar `MAILER_DSN` cap a Mailtrap/Mailhog o un SMTP de desenvolupament i comprovar la bústia.

Com funciona (resum)
- El backend crea una Checkout Session (import fix 20€).
- Stripe redirigeix l'usuari a la pàgina de pagament.
- En `checkout.session.completed`, Stripe envia un webhook al nostre endpoint `/payment/webhook`.
- El webhook valida la firma i, si tot és correcte, envia un correu de "gràcies".

Depuració ràpida
- Si no arriba webhook: comprovar `STRIPE_WEBHOOK_SECRET` i la línia de `stripe listen`.
- Si no s'envia correu: comprovar `MAILER_DSN` i els logs de Symfony.
- Si `composer install` falla: revisar missatges, instal·lar ext-mbstring o córrer les actualitzacions localment.

Notes addicionals
- Hem afegit `stripe/stripe-php` a `composer.json` però no s'ha actualitzat `composer.lock` al repo (problemes de plataforma/paquets). Actualitzeu el lock localment abans de fer proves d'integració complerta.
- No commitejar `.env.local` ni claus privades.


---
Fitxer generat automàticament per l'agent per ajudar a provar la integració.
