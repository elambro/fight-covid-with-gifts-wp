/*

 Dependencies for package.json:

 {
 "dependencies": {
 "query-string": "6.*",
 "request": "2.*",
 "@sendgrid/mail": "6.*"
 }
 }

 */

const querystring = require('querystring');
const request     = require('request');
const sgMail      = require('@sendgrid/mail');

const sandbox = true;

const PRODUCTION_VERIFY_URI = 'https://ipnpb.paypal.com/cgi-bin/webscr';
const SANDBOX_VERIFY_URI    = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

function getPaypalURI() {
    return sandbox ? SANDBOX_VERIFY_URI : PRODUCTION_VERIFY_URI;
}

exports.processIPN = (req, res) => {

    if (req.method !== 'POST') {
        res.status(405).send('Method Not Allowed');
    } else {
        res.status(200).end();
    }

    let ipnTransactionMessage = req.body;
    let formUrlEncodedBody    = querystring.stringify(ipnTransactionMessage);
    let verificationBody      = `cmd=_notify-validate&${formUrlEncodedBody}`;

    let options = {
        method: 'POST',
        uri   : getPaypalURI(),
        body  : verificationBody,
    };

    request(options, function callback(error, response, body) {

        if (!error && response.statusCode == 200) {

            if (body === 'VERIFIED') {

                console.log(`Verified IPN for transaction ID ${ipnTransactionMessage.txn_id}.`);
                sgMail.setApiKey(process.env.SENDGRID_API_KEY);

                let customData    = ipnTransactionMessage.custom.split('|');
                let customerEmail = customData[0];
                let businessName  = customData[1];
                let giftCode      = Math.random().toString(36).slice(-7).toUpperCase();

                const businessMsg = {
                    to     : ipnTransactionMessage.business,
                    from   : ipnTransactionMessage.business,
                    subject: `New gift code order – ${customerEmail} - \$${ipnTransactionMessage.mc_gross} - ${giftCode}`,
                    text   : `New gift code order:\n\nE-mail: ${customerEmail}\nAmount: \$${ipnTransactionMessage.mc_gross}\nGift code: ${giftCode}`,
                    html   : `New gift code order:<br><br><strong>E-mail</strong>: ${customerEmail}<br><strong>Amount</strong>: \$${ipnTransactionMessage.mc_gross}<br><strong>Gift code</strong>: ${giftCode}`,
                };
                const customerMsg = {
                    to     : customerEmail,
                    from   : ipnTransactionMessage.business,
                    subject: `Your Gift Code – ${businessName}`,
                    text   : `Thank you for purchasing a \$${ipnTransactionMessage.mc_gross} gift code from ${businessName}! Here it is:\n\n${giftCode}\n\nIf you have any questions at all, please reply to this e-mail.\n\nBest wishes,\n\nThe ${businessName} Team`,
                    html   : `Thank you for purchasing a \$${ipnTransactionMessage.mc_gross} gift code from ${businessName}! Here it is:<br><br><strong>${giftCode}</strong><br><br>If you have any questions at all, please reply to this e-mail.<br><br>Best wishes,<br><br>The ${businessName} Team`,
                };
                sgMail.send(businessMsg).then(() => {}, console.error);
                console.log(`E-mail sent to the business.`);
                sgMail.send(customerMsg).then(() => {}, console.error);
                console.log(`E-mail sent to the customer.`);

            } else if (body === 'INVALID') {
                console.error(`Invalid IPN received for transaction ID ${ipnTransactionMessage.txn_id}.`);
            } else {
                console.error('Unexpected reponse body.');
            }

        } else {
            console.error(error);
            console.log(body);
        }
    });

};