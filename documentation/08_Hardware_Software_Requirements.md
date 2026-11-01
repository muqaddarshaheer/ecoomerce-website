# Hardware and Software Requirements

The Aptech brief gives a minimum machine. This project was built and checked on a current XAMPP machine. Both are listed. The running site is the second table.

## Hardware required by the brief

- A minimum computer system that can access the tools in the course: a Pentium 166 or better.
- 128 megabytes of RAM or better.

## Hardware used to build and demonstrate Paperlane

| Item | Used for this project |
| --- | --- |
| Processor | A current 64-bit PC processor |
| Memory | 4 GB RAM or more |
| Disk | About 200 MB for XAMPP, this project and its images |
| Display | 1280 × 720 or wider; the shop layout also fits a phone width |
| Network | None required after Bootstrap and jQuery are already in `assets/vendor` |

The site is served on the same PC as the browser.

## Operating system required by the brief

- Linux, or Windows 2000 Server or higher.

## Operating system used

- Windows 10 / 11, with Apache from XAMPP. Linux can host the same PHP files and the same MySQL schema.

## Software required by the brief

- PHP
- MySQL
- Perl
- Apache

## Software used

| Software | Role | Version used while building |
| --- | --- | --- |
| Apache | Web server | The XAMPP Apache service |
| PHP | All application logic | 8.3.33. PHP 8.0 or newer is required |
| MariaDB | MySQL-compatible database | 10.4.32, database `paperlane_shop` |
| Browser | Shop, admin and employee desks | Chrome, Edge or Firefox |
| phpMyAdmin | Optional import tool | Included with XAMPP |

Perl is named in the Aptech software list. This shopping cart does not contain a Perl program. Every page, order rule, cart and report is core PHP, which is also on that list. Apache serves the PHP. MariaDB stores the data in place of a separate MySQL server.

Bootstrap 5.3.3 and jQuery 3.7.1 are stored in the project. The shop does not need a CDN for those files. Headings use Fraunces and body text uses Manrope, requested from Google Fonts, with Georgia and Segoe UI if that request fails.

## Development constraints

- Core PHP only. No Composer framework and no Node build step.
- One database, created from `database.sql`. Do not import that file again on a live shop: it drops `paperlane_shop`.
- Configuration is `config/database.php` and `config/config.php`.
- Time zone is `Asia/Karachi`. Currency is rupees.
