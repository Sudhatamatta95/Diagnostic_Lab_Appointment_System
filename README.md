# DiagnosCare V5 — Diagnostic Lab Appointment and Payment System

Professional TY BBACA project built with PHP, MySQL, Bootstrap 5 and JavaScript.

## V5 features
- Patient, technician, staff and admin login
- Patient booking, payment status, technician assignment and reports
- Admin patient record view with complete test/payment/report history
- Public About page with technician profiles, certificates, awards and lab gallery
- Patient review system with admin approval
- Lab infrastructure, patient-testing and award gallery sections
- Lab address, contact details and Google Maps embed
- Razorpay-ready online payment integration with server-side order creation and signature verification

## Upgrade the existing database
Do NOT import the original `database/diagnostic_lab.sql` again if your database already exists.
Instead import `database/upgrade_v5.sql` once in phpMyAdmin against the existing `diagnostic_lab` database.

## Demo accounts
- Admin: admin@diagnosticlab.test / admin123
- Technician: technician@diagnosticlab.test / tech123
- Staff: staff@diagnosticlab.test / staff123
- Patient: patient@diagnosticlab.test / patient123

## Real payment setup
The project uses Razorpay Standard Checkout. Razorpay supports cards, UPI, net banking and other payment methods. For the college project, first use Razorpay Test Mode. Put your test Key ID and Key Secret into `config/payment.php`.

For real money, use your activated Razorpay LIVE credentials, HTTPS, and a proper production domain. Never publish the Key Secret or commit it to GitHub. Razorpay's security guidance specifically recommends keeping secrets out of source control and validating payment signatures. See the official docs: https://razorpay.com/docs/

## Replace demo images/details
All image placeholders are local SVGs under `assets/images/`. You can replace them with your real JPG/PNG photos while keeping the same filenames, or update the paths in `about.php` and `index.php`.

Replace the demo address, phone and email in `about.php` and `partials/footer.php` before final submission.
