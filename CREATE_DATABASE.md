# Create MySQL Database for CAS Private Care

## Steps:

1. **Open phpMyAdmin** (http://localhost/phpmyadmin)

2. **Click "New" in the left sidebar**

3. **Create database:**
   - Database name: `cas_db`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

4. **Run migrations:**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Done!** Your CAS Private Care system is now using MySQL.

## Or use SQL directly:

In phpMyAdmin SQL tab, run:
```sql
CREATE DATABASE cas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then run: `php artisan migrate:fresh --seed`