# Installing Composer on macOS

## Option 1: Using Homebrew (Recommended)

If you have Homebrew installed:

```bash
brew install composer
```

## Option 2: Direct Installation

1. Download the Composer installer:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
```

2. Verify the installer:
```bash
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
```

3. Run the installer:
```bash
php composer-setup.php
```

4. Move Composer to a global location:
```bash
sudo mv composer.phar /usr/local/bin/composer
```

5. Clean up:
```bash
php -r "unlink('composer-setup.php');"
```

## Option 3: Install PHP First (if not installed)

If PHP is not installed, install it first:

```bash
# Using Homebrew
brew install php

# Then install Composer
brew install composer
```

## Verify Installation

After installation, verify Composer is working:

```bash
composer --version
```

## Install Project Dependencies

Once Composer is installed, navigate to the backend-php directory and run:

```bash
cd backend-php
composer install
```

## Troubleshooting

### If you get "command not found" after installation:

1. Make sure `/usr/local/bin` is in your PATH:
```bash
echo $PATH
```

2. If not, add it to your shell profile:
```bash
echo 'export PATH="/usr/local/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

### Alternative: Use Composer locally

If you can't install globally, you can use it locally:

```bash
# Download composer.phar
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Use it with php
php composer.phar install
```
