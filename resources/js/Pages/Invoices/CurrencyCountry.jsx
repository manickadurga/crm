// src/CurrencyCountry.js
import React from 'react';
import currencyCodes from 'currency-codes';
import { countries } from 'country-data';

const CurrencyCountry = () => {
  const getCountryCurrency = (countryCode) => {
    const country = countries[countryCode];
    if (country && country.currencies) {
      const currencyCode = country.currencies[0];
      const currency = currencyCodes.code(currencyCode);
      return currency ? currency.currency : 'Unknown currency';
    }
    return 'Unknown country';
  };

  return (
    <div>
      <h1>Country and Currency Information</h1>
      <ul>
        {Object.keys(countries).map((countryCode) => (
          <li key={countryCode}>
            {countries[countryCode].name} - {getCountryCurrency(countryCode)}
          </li>
        ))}
      </ul>
    </div>
  );
};

export default CurrencyCountry;
