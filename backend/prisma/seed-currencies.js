import { PrismaClient } from '@prisma/client'

const prisma = new PrismaClient()

const currencies = [
  { code: 'USD', name: 'US Dollar', symbol: '$', isDefault: true, decimalPlaces: 2 },
  { code: 'EUR', name: 'Euro', symbol: '€', isDefault: false, decimalPlaces: 2 },
  { code: 'GBP', name: 'British Pound', symbol: '£', isDefault: false, decimalPlaces: 2 },
  { code: 'JPY', name: 'Japanese Yen', symbol: '¥', isDefault: false, decimalPlaces: 0 },
  { code: 'AUD', name: 'Australian Dollar', symbol: 'A$', isDefault: false, decimalPlaces: 2 },
  { code: 'CAD', name: 'Canadian Dollar', symbol: 'C$', isDefault: false, decimalPlaces: 2 },
  { code: 'CHF', name: 'Swiss Franc', symbol: 'CHF', isDefault: false, decimalPlaces: 2 },
  { code: 'CNY', name: 'Chinese Yuan', symbol: '¥', isDefault: false, decimalPlaces: 2 },
  { code: 'INR', name: 'Indian Rupee', symbol: '₹', isDefault: false, decimalPlaces: 2 },
  { code: 'PKR', name: 'Pakistani Rupee', symbol: '₨', isDefault: false, decimalPlaces: 2 },
  { code: 'AED', name: 'UAE Dirham', symbol: 'د.إ', isDefault: false, decimalPlaces: 2 },
  { code: 'SAR', name: 'Saudi Riyal', symbol: '﷼', isDefault: false, decimalPlaces: 2 },
  { code: 'SGD', name: 'Singapore Dollar', symbol: 'S$', isDefault: false, decimalPlaces: 2 },
  { code: 'HKD', name: 'Hong Kong Dollar', symbol: 'HK$', isDefault: false, decimalPlaces: 2 },
  { code: 'NZD', name: 'New Zealand Dollar', symbol: 'NZ$', isDefault: false, decimalPlaces: 2 },
  { code: 'SEK', name: 'Swedish Krona', symbol: 'kr', isDefault: false, decimalPlaces: 2 },
  { code: 'NOK', name: 'Norwegian Krone', symbol: 'kr', isDefault: false, decimalPlaces: 2 },
  { code: 'DKK', name: 'Danish Krone', symbol: 'kr', isDefault: false, decimalPlaces: 2 },
  { code: 'PLN', name: 'Polish Zloty', symbol: 'zł', isDefault: false, decimalPlaces: 2 },
  { code: 'TRY', name: 'Turkish Lira', symbol: '₺', isDefault: false, decimalPlaces: 2 },
  { code: 'BRL', name: 'Brazilian Real', symbol: 'R$', isDefault: false, decimalPlaces: 2 },
  { code: 'MXN', name: 'Mexican Peso', symbol: '$', isDefault: false, decimalPlaces: 2 },
  { code: 'ZAR', name: 'South African Rand', symbol: 'R', isDefault: false, decimalPlaces: 2 },
  { code: 'RUB', name: 'Russian Ruble', symbol: '₽', isDefault: false, decimalPlaces: 2 },
  { code: 'KRW', name: 'South Korean Won', symbol: '₩', isDefault: false, decimalPlaces: 0 },
  { code: 'THB', name: 'Thai Baht', symbol: '฿', isDefault: false, decimalPlaces: 2 },
  { code: 'MYR', name: 'Malaysian Ringgit', symbol: 'RM', isDefault: false, decimalPlaces: 2 },
  { code: 'IDR', name: 'Indonesian Rupiah', symbol: 'Rp', isDefault: false, decimalPlaces: 2 },
  { code: 'PHP', name: 'Philippine Peso', symbol: '₱', isDefault: false, decimalPlaces: 2 },
  { code: 'EGP', name: 'Egyptian Pound', symbol: 'E£', isDefault: false, decimalPlaces: 2 },
]

async function seedCurrencies() {
  console.log('Seeding currencies...')

  for (const currency of currencies) {
    await prisma.currency.upsert({
      where: { code: currency.code },
      update: currency,
      create: currency,
    })
    console.log(`✓ Seeded currency: ${currency.code}`)
  }

  console.log('Currency seeding completed!')
}

seedCurrencies()
  .catch((error) => {
    console.error('Error seeding currencies:', error)
    process.exit(1)
  })
  .finally(async () => {
    await prisma.$disconnect()
  })
