# odigi-web

![GitHub stars](https://img.shields.io/github/stars/aimeennnn/odigi-web?style=for-the-badge&logo=github) ![GitHub forks](https://img.shields.io/github/forks/aimeennnn/odigi-web?style=for-the-badge&logo=github) ![GitHub issues](https://img.shields.io/github/issues/aimeennnn/odigi-web?style=for-the-badge&logo=github) ![Last commit](https://img.shields.io/github/last-commit/aimeennnn/odigi-web?style=for-the-badge&logo=github) ![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=white) ![Node.js](https://img.shields.io/badge/Node.js-339933?style=for-the-badge&logo=nodedotjs&logoColor=white) ![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white) ![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white) ![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white) ![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white) ![License](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)

## 📝 Description

odigi-web — a cli tool built with Docker, JavaScript, Node.js, PHP, Python, Tailwind CSS, Vite.

## 🛠️ Tech Stack

- 🐳 **Docker**
- 🟨 **JavaScript**
- ⬢ **Node.js**
- 🐘 **PHP**
- 🐍 **Python**
- 🌬️ **Tailwind CSS**
- ⚡ **Vite**

**Notable libraries:** Laravel

## ⚡ Quick Start

```bash

# 1. Clone the repository
git clone https://github.com/aimeennnn/odigi-web.git

# 2. Install dependencies
npm install

# 3. Configure environment
cp .env.example .env   # then fill in the values

# 4. Start the dev server
npm run dev
```

## 🛠️ Development Setup

### Node.js / JavaScript
1. Install Node.js (v18+ recommended)
2. Install dependencies: `npm install` (or `yarn` / `pnpm install` / `bun install`)
3. Start the dev server: see the **Quick Start** above

### Python
1. Install Python (v3.10+ recommended)
2. `python -m venv venv && source venv/bin/activate`  (Windows: `venv\Scripts\activate`)
3. `pip install -r requirements.txt`

### Docker
1. `docker build -t my-app .`
2. `docker run -p 3000:3000 my-app`

## 🚢 Deployment

### Docker
```bash
docker build -t odigi-web .
docker run -p 3000:3000 odigi-web
```

## 👥 Contributing

Contributions are welcome! Here's the standard flow:

1. **Fork** the repository
2. **Clone** your fork: `git clone https://github.com/aimeennnn/odigi-web.git`
3. **Branch**: `git checkout -b feature/your-feature`
4. **Commit**: `git commit -m 'feat: add some feature'`
5. **Push**: `git push origin feature/your-feature`
6. **Open** a pull request

Please follow the existing code style and include tests for new behavior where applicable.

## 📜 License

This project is licensed under the **MIT** License.

---
*This README was generated with ❤️ by [ReadmeBuddy](https://readmebuddy.com)*
