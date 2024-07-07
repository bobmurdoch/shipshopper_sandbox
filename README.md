## What even is this?
ShipShopper will be a package for PHP apps that will support
address validation, quoting, and label purchases from various shipping carriers.

## What is the status?
The project is a work in progress. As of writing this readme, nothing is functional.
I am currently building it in a Laravel app for easier  manual testing as I build the API.
My plan is to start with address validation, then move to  quoting and purchasing. One goal is to use Laravel's
Http pool feature to be able to make multiple requests simultaneously for address validation and quoting as this 
is a feature I don't have experience with implementing/testing, and I'd like to learn it.

To start, only US addresses are supported and UPS, Fedex and USPS (via Stamps.com).
Without over-optimizing for the unknown future, I am adding in interfaces/methods
in some places that will make it easier to support other countries and carriers as I'd
like to support Canada soon after US support is complete.

## Notes
I'm putting what the code will be for the library in \App\ShipShopper for now and adding tests for
that code. For the client code I'm using to test the library, I'm not adding tests.
