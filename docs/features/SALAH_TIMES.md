# Salah Time Calculation System

## Overview
The Salah Time Calculation System provides accurate Islamic prayer times using advanced astronomical calculations. This document outlines the features, implementation details, and usage of the system.

## Features

### Core Functionality
- **Multiple Calculation Methods**: Supports various calculation methods (MWL, ISNA, EGYPT, MAKKAH, etc.)
- **High Accuracy**: Uses precise astronomical formulas for prayer time determination
- **Global Coverage**: Works for locations worldwide, including extreme latitudes
- **Daylight Saving Time**: Automatically adjusts for DST where applicable
- **Time Zone Support**: Handles all time zones and UTC offsets

### Calculation Methods
- **Fajr**: Dawn, when the sky begins to lighten
- **Sunrise**: When the upper edge of the sun appears on the horizon
- **Dhuhr**: Midday, when the sun has passed its zenith
- **Asr**: Afternoon prayer time (multiple calculation methods supported)
- **Maghrib**: Sunset
- **Isha**: Nightfall, when the sky is completely dark

## Implementation

### Key Components
- **SalahTimeCalculator**: Main class handling all prayer time calculations
- **Astronomical Calculations**: Methods for solar position, equation of time, and declination
- **Time Zone Handling**: Proper handling of time zones and DST
- **Edge Case Management**: Special handling for high latitudes and polar days/nights

### Usage Example

```php
use Core\Islamic\SalahTimeCalculator;

// Initialize calculator
$calculator = new SalahTimeCalculator();

// Calculate prayer times
$times = $calculator->calculateTimes(
    $latitude,          // float: Location latitude
    $longitude,         // float: Location longitude
    $year,              // int: Gregorian year
    $month,             // int: Gregorian month (1-12)
    $day,               // int: Day of month (1-31)
    $calculationMethod, // string: Calculation method (e.g., 'MWL', 'ISNA')
    $timezone           // int: Timezone offset in hours
);

// Access prayer times
$fajr = $times['fajr'];
$sunrise = $times['sunrise'];
$dhuhr = $times['dhuhr'];
$asr = $times['asr'];
$maghrib = $times['maghrib'];
$isha = $times['isha'];
```

## Testing

### Test Coverage
The system includes comprehensive tests covering:
- Multiple locations worldwide
- Different calculation methods
- Edge cases (polar day/night, high latitudes)
- Date boundaries and time zone transitions
- DST changes

### Running Tests
```bash
php tests/TestSalahTimes.php
```

## Recent Updates

### v0.0.54
- Fixed prayer time order issues
- Improved time zone and DST handling
- Enhanced calculation accuracy for high latitudes
- Added comprehensive test coverage
- Fixed day transition handling

## References
- [Astronomical Algorithms by Jean Meeus](https://www.willbell.com/math/mc1.htm)
- [Prayer Times Calculation Methods](https://www.moonsighting.com/pray.php)
- [Islamic Finder API](https://www.islamicfinder.org/developer/)

## License
This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
