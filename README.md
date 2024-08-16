# Bank-Central-Asia-BCA-Currency-Exchange-Rates
Bank Central Asia Currency Exchange Rates , auto update in once per 1 minutes

# Screenshot

![Screenshot_16](https://github.com/user-attachments/assets/ccb9d95b-f9cd-49e2-b89b-5b29efc98941)

# Currency Exchange Rates

This project provides a web interface to display and track currency exchange rates from Bank Central Asia (BCA). It fetches real-time currency data from the BCA API and displays both current rates and percentage changes in a web application.

## Features

- Fetch and display current currency exchange rates.
- Show percentage changes in rates compared to the previous data.
- Auto-refreshes data every 60 seconds.
- Manual update option with a "Force Update" button.
- Styled using Tailwind CSS for a responsive design.

## Files

- `bca.php`: Contains both PHP and HTML code for fetching and displaying currency exchange rates.
- `history.json`: Stores historical exchange rates data for comparison purposes.

## Setup

### Prerequisites

- PHP 7.4 or higher
- A web server (e.g., Apache, Nginx) with PHP support
- An internet connection to fetch data from the BCA API

### Installation

1. Just Clone this repo


## How It Works

1. **Data Fetching**:
   - **Initial Load & Manual Update**: When the page loads or when the "Force Update" button is clicked, the PHP script makes a request to the BCA API to fetch the latest currency exchange rates.
   - **API Request**: This is achieved using a cURL request with the appropriate headers and POST data.
   - **Response Handling**: The API response is decoded from JSON format and processed by the script.

2. **Data Storage**:
   - **Saving Data**: The fetched currency rates are saved to a file named `history.json`. This file keeps a record of the latest data for historical comparison.
   - **Historical Data**: Previous data from `history.json` is also loaded to compare with the newly fetched rates.

3. **Displaying Data**:
   - **HTML Tables**: The current exchange rates and percentage changes are displayed in two tables:
     - **Current Rates Table**: Shows the latest exchange rates for various currencies.
     - **Percentage Change Table**: Displays the percentage change in exchange rates compared to the last fetched data.
   - **Table Updates**: Data is rendered dynamically into the HTML tables using JavaScript.

4. **Automatic Refresh**:
   - **Interval**: The page automatically fetches updated data every 60 seconds.
   - **Update Process**: The data is fetched and displayed without needing a page refresh, providing a seamless user experience.

5. **Manual Update**:
   - **Button Interaction**: Clicking the "Force Update" button manually triggers a data fetch.
   - **Loading State**: The button is disabled and shows a loading state while the data is being fetched to indicate that an update is in progress.

6. **Error Handling**:
   - **Fetch Errors**: Errors encountered during the data fetch are displayed on the page, ensuring that users are informed of any issues.
   - **Display Updates**: The interface provides feedback on the status of data fetching, including success and error messages.

## Usage

- **Automatic Updates**: The page will refresh data every 60 seconds without user intervention.
- **Manual Updates**: Use the "Force Update" button to immediately fetch and display the latest data.

## Troubleshooting

- **No Data Displayed**: Ensure that the BCA API endpoint is reachable and returns valid data. Check for any errors in the browser console.
- **Permissions Issues**: Verify that the `history.json` file has appropriate write permissions for the web server.

## TODO
- Need work for Percentage Change in the Last Hour / Minute / Months / Year

## Contact

For any issues or questions, please contact [MUGHU](mailto:kontak@mughu.biz.id).
