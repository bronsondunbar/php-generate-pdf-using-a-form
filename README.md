# Generate PDF using a form

This example creates a PDF using the <a href="http://www.fpdf.org/" target="_blank">FPDF library</a> with the content from a form.

The content is 64 encoded and stored in a database as well.

It also includes Google reCAPTCHA to ensure there is no spam content created.

The PDF can also be emailed as an attachment.

Preview: http://bit.ly/2iCH8tt

## Steps

In order for the example below to work you will need to use your own Google reCAPTCHA site &amp; private keys as well as your MailChimp API key and list ID

1. Go to <a href="https://www.google.com/recaptcha/intro/">https://www.google.com/recaptcha/intro/</a>
2. Click on the Get reCAPTCHA button
3. After logging in, you will need to register your site in order to get your keys.
4. Once you have done this, you can select your site and then click on the Keys section
5. This will give you both your site &amp; private keys
6. In index.html, you can search for SITE_KEY and replace it with your key
7. In create.php, you can search for PRIVATE_KEY and replace it with your key
8. Once this has been done, you can upload the files your site and test. Keep in mind you need to upload the files to the same site you added in Google reCAPTCHA
9. You will need to add your own database details in the db.php found in the includes folder in order the content to also be captured there.
