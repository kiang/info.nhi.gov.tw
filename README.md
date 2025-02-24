# Taiwan Emergency Rooms Status Archive

This project archives status information of all emergency rooms in Taiwan. The data is sourced from Taiwan's National Health Insurance Administration (NHI).

## Data Source

The data is retrieved from [NHI's Emergency Room Information System](https://info.nhi.gov.tw/INAE1000/INAE1000S00) and is available under CC-BY license.

## Project Structure

- `scripts/`: Contains PHP scripts for data fetching and processing
  - `01_fetch.php`: Fetches hospital information from NHI API
  - `01_fetch_er.php`: Fetches real-time emergency room status for each hospital
  - `02_geojson.php`: Combines all hospital data into a single GeoJSON file
- `raw/`: Contains raw data files
  - `map.json`: Cached mapping data
  - `json/`: Organized JSON files by hospital type and city

## Requirements

- PHP with Composer
- Symfony components (BrowserKit, HttpClient)

## Installation

```bash
composer install
```

## Usage

1. First, fetch the hospital data:
```bash
php scripts/01_fetch.php
```

2. Then, fetch emergency room status:
```bash
php scripts/01_fetch_er.php
```

3. Generate combined GeoJSON file:
```bash
php scripts/02_geojson.php
```

## License

- Scripts: MIT License (see LICENSE file)
- Data: CC-BY License from National Health Insurance Administration, Taiwan

## Author

Finjon Kiang 