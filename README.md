# kudix

## issues

- add documentable ID on the invoices and receipts to show which purchase or sale owns this.
- do not update the inventory until the purchase order status has been set to received. (IN_PROGRESS)
- downloading pdfs needs to be swift. i implemented laravel/dompdf and removed spatie/laravel-pdf (the latter was very slow at generating pdfs). pdfs are being generated but the styles are not working. one way to fix it is to use a stylesheet you wrote (laraveldaily). but i'm trying to reference the built css and it's still not working.
- your documents need to differentiate btw themselves. purchases have to have supplier and not client. sales is kinda fine.
- remove Kudix, Inc., from the header and put the company's address. add ur logo to the bottom left side.

### Sleep on it
<!-- !IMPORTANT -->
the customer table suppose to be in the users table and not a separate one. because, they'll have to log in to check their stats and stuff.
