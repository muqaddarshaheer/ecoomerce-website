# Hardware and Software Requirements

## Hardware

These are enough for a classroom demonstration on one computer.

| Item | Minimum used for this project |
| --- | --- |
| Processor | Dual-core processor |
| Memory | 4 GB RAM |
| Disk | About 200 MB for XAMPP plus this project and its images |
| Display | 1280 × 720 or wider; the layout also fits a phone width |
| Network | None required after the page libraries are already in `assets/vendor` |

The site is served on the same PC as the browser. A second computer is not required.

## Software

| Software | Role | Version used while building |
| --- | --- | --- |
| Windows | Operating system | Windows 10 / 11 |
| XAMPP | Apache, PHP and MariaDB together | Local XAMPP install |
| Apache | Web server | The XAMPP Apache service |
| PHP | Application language | 8.3 (8.0 or newer is required) |
| MariaDB | Database | 10.4 |
| Browser | Chrome, Edge or Firefox | Current desktop browser |
| phpMyAdmin | Optional import tool | Included with XAMPP |

Bootstrap 5.3.3 and jQuery 3.7.1 are stored in the project. The site does not need a CDN for those files. The display fonts are requested from Google Fonts, with Georgia and Segoe UI as the fallback if that request fails.

## Development constraints

- Core PHP only. No Composer framework and no Node build step.
- One MySQL database, imported from `database.sql`.
- Configuration is two PHP files: `config/database.php` and `config/config.php`.
