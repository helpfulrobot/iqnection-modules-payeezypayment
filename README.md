# Payeezy Payment Gateway Module

Payeezy has a very nice API, but figuring out how to get one setup can be kind of tricky

Vists Payeezy developer site to create a developer account (https://developer.payeezy.com/). Setup test API (https://developer.payeezy.com/user/me/apps). Once you get the API key and token, add them to the appropriate fields in the site config. Visit the Merchant tab on payeezy (https://developer.payeezy.com/user/me/merchants) to find a test merchant with a token. Set your installation to Certify mode and you can test the integration. When you're integration is working, you'll need to certify the build before you can add live merchants. Under your Account tab, there should be a Get Certified link, be sure to complete all of your profile information before you can proceed with certification. While the system is in Certify mode, all transaction requests will be stored in a debug log in your site root. You'll need to copy one of these transactions to submit as your certification request, then submit the same exact request to certify your installation. Once your installation is certified, you can add live merchants and use the sandbox setting. 
At go live, make sure the clients API and merchant keys are in the site config and adjust the setting to Live

### Run Test credit card transactions:
https://developer.payeezy.com/payeezy-api/apis/post/transactions-3