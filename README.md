# 🎮 3D Tic-Tac-Toe | AWS EC2 Deployment

### An Interactive 3D Web Gaming Application

**Marwadi University | Cloud Computing Project**

A modern, interactive **3D Tic-Tac-Toe game** built with PHP, JavaScript, Three.js, HTML, and CSS, deployed on an **Amazon Web Services (AWS) EC2 instance**.

The project combines interactive 3D graphics with cloud infrastructure by displaying real-time server information retrieved directly from the AWS EC2 Instance Metadata Service (IMDSv2).

---

## 🌟 Features

- **3D Tic-Tac-Toe Gameplay:** Interactive 3×3×3 gaming board containing 27 playable positions.
- **3D Graphics:** Three.js-powered interactive cubes with camera rotation and zoom.
- **Two-Player Mode:** Local multiplayer with alternating X and O turns.
- **Win Detection:** Supports all 49 valid winning combinations, including rows, columns, and 3D diagonals.
- **Score Tracking:** Automatically records both players' winning scores during the session.
- **New Game:** Restart gameplay without refreshing the page.
- **AWS EC2 Integration:** Retrieves genuine EC2 instance details through server-side PHP.
- **Responsive Interface:** Compatible with desktop, tablet, and mobile browsers.
- **Modern Design:** Professional white-and-teal interface with Marwadi University branding.
- **Single-File Application:** The complete application is implemented in `index.php`.

## 🛠️ Technology Stack

| Technology | Purpose |
|---|---|
| HTML5 | Web application structure |
| CSS3 | Responsive design and styling |
| JavaScript | Game logic and user interactions |
| Three.js | 3D graphics, cubes, and rendering |
| PHP | Server-side processing |
| AWS EC2 | Cloud hosting |
| AWS IMDSv2 | Server instance metadata |
| Apache2 | Web server |
| Ubuntu Linux | Server operating system |
| GitHub | Source code management |

## 📁 Project Structure

```text
3D-Tic-Tac-Toe-AWS-EC2/
│
├── index.php
└── README.md
```

The `index.php` file contains all application PHP, HTML, CSS, and JavaScript code.

Three.js and Google Fonts are loaded through external CDNs.

## ☁️ AWS EC2 Server Information

The application retrieves actual EC2 instance information directly from the server.

| Server Information | AWS Metadata Endpoint |
|---|---|
| Public IPv4 | `/latest/meta-data/public-ipv4` |
| Private IPv4 | `/latest/meta-data/local-ipv4` |
| Instance ID | `/latest/meta-data/instance-id` |
| Availability Zone | `/latest/meta-data/placement/availability-zone` |

### How It Works

The PHP application communicates with the AWS Instance Metadata Service using IMDSv2.

1. PHP requests a session token from the EC2 Instance Metadata Service.
2. AWS returns a metadata session token.
3. PHP uses the token to retrieve the instance details.
4. The server processes the returned metadata.
5. The information appears in the application's Server Information card.

**Important:** Public and private IP addresses are fetched directly from the EC2 instance, not from the visitor's browser, JavaScript, or client IP address.

If a metadata field is inaccessible, the application displays `Unavailable`.

## 🚀 Deployment on AWS EC2

### Step 1: Launch an EC2 instance

Create an AWS EC2 instance with the following configuration:

- Operating System: Ubuntu Server
- Instance Type: A suitable instance such as `t3.micro`
- Web Server: Apache2
- Backend: PHP
- Network: HTTP access on port 80
- Instance Metadata Service: Enabled with IMDSv2 support

Ensure that the instance has a public IP address and that its security group permits the required traffic.

### Step 2: Connect to your EC2 instance

Connect using SSH:

```bash
ssh -i "your-key.pem" ubuntu@YOUR_EC2_PUBLIC_IP
```

Replace the key filename and IP address with your actual instance details.

### Step 3: Update Ubuntu

```bash
sudo apt update
sudo apt upgrade -y
```

### Step 4: Install Apache and PHP

```bash
sudo apt install apache2 php libapache2-mod-php php-curl -y
```

### Step 5: Enable Apache

```bash
sudo systemctl enable apache2
sudo systemctl start apache2
```

Verify the service:

```bash
sudo systemctl status apache2
```

### Step 6: Deploy the application

Copy `index.php` into the Apache document root:

```bash
sudo cp index.php /var/www/html/index.php
```

Alternatively, edit the file directly:

```bash
sudo nano /var/www/html/index.php
```

Paste the application code and save the file.

### Step 7: Verify PHP syntax

```bash
php -l /var/www/html/index.php
```

Expected output:

```text
No syntax errors detected in /var/www/html/index.php
```

### Step 8: Restart Apache

```bash
sudo systemctl restart apache2
```

### Step 9: Open the application

Navigate to:

```text
http://YOUR_EC2_PUBLIC_IP/
```

Your interactive 3D Tic-Tac-Toe application should now be accessible.

## 🎯 How to Play

1. Player X starts the game.
2. Click or tap an available cube on the 3D board.
3. Player O takes the next turn.
4. Rotate the board to view different positions and layers.
5. The first player to complete three matching marks along a valid straight 3D line wins.
6. The game displays the winner and updates the scoreboard.
7. Click **New Game** to restart the board.

### Game Controls

| Action | Control |
|---|---|
| Place X or O | Click or tap an empty cube |
| Rotate Board | Mouse drag or touch drag |
| Zoom | Mouse wheel or pinch |
| Restart | New Game button |

## 🏗️ Application Architecture

```text
          USER / WEB BROWSER
                  |
                  | HTTP Request
                  v
          AWS EC2 INSTANCE
                  |
            Apache Web Server
                  |
              index.php
                  |
         +--------+---------+
         |                  |
         v                  v
   PHP Backend        HTML / CSS / JS
         |                  |
         v                  v
   AWS IMDSv2          Three.js Engine
         |                  |
         v                  v
   Real EC2 Data       3D Game Board
         |                  |
         +--------+---------+
                  |
                  v
          Browser Interface
```

PHP retrieves EC2 metadata on the server. The browser executes JavaScript and Three.js to render and operate the game.

## 🔐 Security Considerations

- Uses AWS IMDSv2 session tokens for instance metadata access.
- Does not use the visitor's IP address to identify the EC2 server.
- Escapes server information before rendering it as HTML.
- Restricts metadata requests to predetermined AWS metadata paths.
- Does not expose the IMDSv2 token to the browser.
- Requires no AWS access keys or credentials in the application code.

**Deployment note:** This project intentionally displays the instance's public IP, private IP, instance ID, and availability zone for educational demonstration. Avoid exposing internal infrastructure details in public production applications unless specifically required.

For production hosting, configure HTTPS, restrict administrative access, and maintain regular operating system and dependency updates.

## 🧪 Testing

Recommended checks:

- Verify the 3D game renders correctly.
- Test both X and O placement.
- Confirm that moves cannot overwrite occupied positions.
- Test all supported winning directions.
- Verify score updates and game reset.
- Test rotation and zoom on mobile and desktop.
- Confirm public and private EC2 IP addresses are displayed correctly.
- Verify that EC2 metadata access works with IMDSv2.
- Check PHP syntax using `php -l`.

## 🏫 Academic Project

Developed as an educational cloud computing and web development demonstration associated with **Marwadi University**.

The project illustrates how web applications can combine modern 3D frontend interfaces with backend processing and AWS cloud infrastructure.

The university shield graphic used in the application is a stylized SVG approximation, not an official logo asset.

## 📚 References

- [Amazon EC2 Documentation](https://docs.aws.amazon.com/ec2/)
- [EC2 Instance Metadata Service](https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/configuring-instance-metadata-service.html)
- [Three.js Documentation](https://threejs.org/docs/)
- [PHP Documentation](https://www.php.net/docs.php)
- [Apache HTTP Server](https://httpd.apache.org/)

## 📄 License

This project is intended for educational and demonstration purposes. No open-source license has been assigned. Add a `LICENSE` file if you wish to grant reuse or distribution rights.

---

**Made for learning and innovation at Marwadi University.**

*3D Tic-Tac-Toe | PHP · JavaScript · Three.js · AWS EC2*