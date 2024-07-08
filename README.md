## What even is this?
ShipShopper will be a package for PHP apps that will support
address validation, quoting, and label purchases from various shipping carriers. The aim will
be to return results that are in a single format where possible so that the differing details
of each carriers' API implementations are not a concern for the user of the package.

## What is the status?
The project is a work in progress. As of writing this readme, nothing is functional.
I am currently building it in a Laravel app for easier  manual testing as I build the API.

The code that will ultimately be moved into the package lives in 
[App/ShipShopperLibrary](https://github.com/bobmurdoch/shipshopper_sandbox/tree/main/app/ShipShopperLibrary) and my
aim is to add tests for that code. "Client" code that is showing how to use the library/me manually testing during
development will live elsewhere in this Laravel app and likely not have tests.

My plan is to start with address validation, then move to  quoting and purchasing. One goal is to use Laravel's
Http pool feature to be able to make multiple requests simultaneously for address validation and quoting as this 
is a feature I don't have experience with implementing/testing, and I'd like to learn it.

To start, only US addresses are supported and UPS, Fedex and USPS (via Stamps.com).
Without over-optimizing for the unknown future, I am adding in interfaces/methods
in some places that will make it easier to support other countries and carriers as I'd
like to support Canada soon after US support is complete.

## Tasks
☑️ UPS Address Validation

☐ UPS Address Validation Tests

☐ USPS (Stamps.com) Address Validation

☐ USPS (Stamps.com) Address Validation Tests

☐ Fedex (Stamps.com) Address Validation

☐ Fedex (Stamps.com) Address Validation Tests
