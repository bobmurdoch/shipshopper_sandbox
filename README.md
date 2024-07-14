## What is this?
ShipShopper will be a package for PHP apps that will support
address validation, quoting, and label purchases from various shipping carriers. The aim will
be to return results that are in a single format where possible so that the differing details
of each carriers' API implementations are not a concern for the user of the package.

Installing the app, adding carrier credentials in the .env file and then visiting /demo in your local environment
will give a page that demos basic functionality of the address validation step at this time.

## What is the status?
The project is a work in progress and the library is incomplete.
I am currently building it in a Laravel app for easier  manual testing as I build the code.

The code that will ultimately be moved into the package lives in 
[App/ShipShopperLibrary](https://github.com/bobmurdoch/shipshopper_sandbox/tree/main/app/ShipShopperLibrary) and my
aim is to add tests for that code. 

"Client" code that demos how to use the library/ and is also a means for me to manually test during
development will live elsewhere in this Laravel app and likely not have tests.

My plan is to start with address validation, then move to  quoting and purchasing. One goal is to use Laravel's
Http pool feature to be able to make multiple requests simultaneously for address validation and quoting as this 
is a feature I don't have experience with implementing/testing, and I'd like to learn it.

To start, only US addresses are supported and UPS, Fedex and USPS (via Stamps.com) are the supported carriers.
While attempting to now over optimize for the unknown future, I am adding in interfaces/methods
in some places that will make it easier to support other countries and carriers as I'd
like to support Canada soon after US support is complete.

## Tasks
☑️ UPS Address Validation

☑️ UPS Address Validation Tests

☐ USPS (Stamps.com) Address Validation

☐ USPS (Stamps.com) Address Validation Tests

☑️ Fedex (Stamps.com) Address Validation

☑️ Fedex (Stamps.com) Address Validation Tests

## Notes
When using the Fedex API, please see their documentation about the limitations
of their testing API endpoints. It may be simpler to just use production credentials
while working with this code.

With UPS, in sandbox mode of their API only California addresses are supported.
