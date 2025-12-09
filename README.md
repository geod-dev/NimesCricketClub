![badge1](https://img.shields.io/badge/Status-Active-brightgreen) 
![badge2](https://img.shields.io/badge/License-MIT-blue) 
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![Website shields.io](https://img.shields.io/website-up-down-green-red/https/nimescricketclub.fr)](https://nimescricketclub.fr/)
![Static Badge](https://img.shields.io/badge/>6.2-fuchsia?label=php)

# 🏏 Nîmes Cricket Club
[nimescricketclub.fr](https://nimescricketclub.fr)

![Homepage Screenshot](https://api.microlink.io/?url=https%3A%2F%2Fnimescricketclub.fr&overlay.browser=dark&overlay.background=linear-gradient%28225deg%2C%23007b45+0%25%2C+%2380eca8+50%25%2C+%23007b45+100%25%29&screenshot=true&embed=screenshot.url)

## 📖 Description
Official website of the **Nîmes Cricket Club**, developed voluntarily for the association.  
This project use **Symfony 6**, **Tailwind CSS**, and **Twig Components**.  
It showcases modern web features while following best practices in web development.

## ✨ Key Features
- 📰 **News & Newsletter**: manage posts with automatic newsletter subscription.  
- 📝 **Dynamic Forms**: advanced forms with dynamic pricing calculation and payment via **Stripe**.  
- 📧 **Contact Form**: directly linked to the association's email.  
- 🌐 **Automatic Translations**: content can be automatically translated using the **Mistral API**.  
- 💻 **Modern & Interactive Frontend**: image zoom with `medium-zoom`, responsive and clean UI.

## 🛠 Tech Stack
- **Backend**: Symfony 6, Doctrine ORM  
- **Frontend**: Tailwind CSS, Twig Components, native JS  
- **Build & Assets**: Webpack Encore  
- **Payment**: Stripe API  
- **Translation**: Mistral API  

## 🚀 Installation
1. Clone the repository:  
```bash
git clone https://github.com/yourusername/nimes-cricket-club.git
cd nimes-cricket-club
```

2. Install PHP dependencies:  
```bash
composer install
```

3. Install JS dependencies and build Webpack:  
```bash
npm install
npm run build
```

4. Configure the database and create the schema:  
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update -f
```

5. Start the Symfony server:  
```bash
symfony server:start
```

## 💡 What I Learned
Although this was a volunteer project, it represents my first **truly structured and clean project**.  
I applied skills acquired previously, including:  
- 📂 Modern Symfony project structure  
- 🧩 Reusable Twig components  
- 📝 Complex form handling and online payment integration  
- 🌐 Integration of external APIs (Mistral for translation, Stripe for payment)
- 🚀 Deployment on Plesk

## 📄 License
This project is licensed under the **MIT License**.
