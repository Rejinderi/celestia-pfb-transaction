# PayForBlob Transaction Submission

This project is a simple web application to submit PayForBlob transactions to a specified node. Users can input data of various types (text, number, or URL), encode the data into a hexadecimal format, generate a random namespace ID, and submit the PayForBlob transaction. Users can also verify the transaction with the provided functionality.

## Features

- Input data in different formats (text, number, or URL)
- Encode data into a hex string
- Generate a random namespace ID
- Submit PayForBlob transactions to a specified node
- Verify the submitted transaction
- Use proxied request to overcome CORS restrictions

## Prerequisites

- Web server (e.g., Apache or Nginx)
- PHP >=7.2.5, <8.3 (compatible with the web server)

## Installation

1. Clone the repository or download the project files.
2. Place the project files in the web server's root directory or a subdirectory.
3. Run `php composer.phar update` to install dependencies.

## Usage

1. Open the web application in a browser.
2. Choose the data type you want to submit (text, number, or URL).
3. Input the data in the provided textarea.
4. Click the "Hex Encode" button to convert the input data into a hex string.
5. Click the "Generate Random" button to generate a random namespace ID.
6. (Optional) Change the Node URL if needed.
7. Click the "Submit PFB" button to submit the PayForBlob transaction.
8. After submitting, click the "Verify Shares" button to verify the submitted transaction.

## License

This project is open source and available under the [MIT License](LICENSE).