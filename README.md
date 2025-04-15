# E-Mart API Documentation

## Getting Started

Sebelum menggunakan API ini, ikuti langkah-langkah berikut:

1. **Buat Role Terlebih Dahulu**  
   Sebelum mendaftarkan akun, buat role terlebih dahulu.  
   - **Create a new role**  
     `POST /api/role`  

2. **Buat Akun (Register)**  
   Setelah role tersedia, daftar akun baru menggunakan endpoint berikut:  
   - **Register**  
     `POST /api/auth/register`  

3. **Login dan Dapatkan Token**  
   Setelah berhasil mendaftar, login untuk mendapatkan token autentikasi.  
   - **Login**  
     `POST /api/auth/login`  

4. **Simpan Access Token**  
   Setelah login, API akan memberikan `access_token`. Simpan token ini, karena semua request **POST**, **PUT**, dan **DELETE** memerlukan **Authorization** menggunakan **Bearer Token**.  

5. **Tambahkan Header Authorization**  
   Setiap request yang membutuhkan autentikasi harus menyertakan **header Authorization** agar bisa diakses. Gunakan token yang diperoleh setelah login sebagai `{access_token}`.  
   
   Tambahkan header berikut pada request yang memerlukan autentikasi:  
   ```
   Authorization: Bearer {access_token}
   Accept: application/json
   ```
   - **Authorization** → Wajib menggunakan **Bearer Token** agar API bisa mengenali user yang sedang mengakses.  
   - **Accept: application/json** → Wajib ditambahkan agar server mengembalikan response dalam format JSON.  
   
   > **Catatan:**  
   > Semua request dengan metode **POST**, **PUT**, dan **DELETE** memerlukan **Authorization** menggunakan Bearer Token.  
   > Jika tidak menyertakan header ini, API akan mengembalikan **error unauthorized (401 Unauthorized)**.

6. **Buat Kategori Sebelum Menambahkan Produk**  
   Sebelum membuat produk, pastikan sudah ada kategori.  
   - **Create a new category**  
     `POST /api/category`  

---

## Authentications
- **Login**  
  `POST /api/auth/login`  
- **Register**  
  `POST /api/auth/register`
- **Logout**  
  `POST /api/auth/logout`  

## Products
- **Get all products**  
  `GET /api/product`  
- **Search products by name**  
  `GET /api/product?nama_product={query}`
- **Get publish products**  
  `GET /api/product?publish`
- **Get draft products**  
  `GET /api/product?draft`   
- **Create a new product**  
  `POST /api/product`  
- **Update a product**  
  `PUT /api/product/{id}`  
- **Delete a product**  
  `DELETE /api/product/{id}`  

## Categories
- **Get all categories**  
  `GET /api/category`  
- **Create a new category**  
  `POST /api/category`  
- **Update a category**  
  `PUT /api/category/{id}`  
- **Delete a category**  
  `DELETE /api/category/{id}`  

## Roles
- **Get all roles**  
  `GET /api/role`  
- **Create a new role**  
  `POST /api/role`  

## Ratings
- **Get product ratings**  
  `GET /api/rating?id_product={id}`  
- **Submit a rating**  
  `POST /api/rating`  

## Transactions
- **Get all transactions**  
  `GET /api/transaction`  
- **Create a new transaction**  
  `POST /api/transaction`  
- **Update a transaction**  
  `PUT /api/transaction/{id}`  
- **Delete a transaction**  
  `DELETE /api/transaction/{id}`  

## Transaction Details
- **Get all transaction details**  
  `GET /api/detail-transaction`  
- **Get transaction details by transaction ID**  
  `GET /api/detail-transaction/{transaction_id}`  
- **Create a new transaction detail**  
  `POST /api/detail-transaction`  
- **Update a transaction detail**  
  `PUT /api/detail-transaction/{id}`  
- **Delete a transaction detail**  
  `DELETE /api/detail-transaction/{id}`
  
## Manage User
- **Get all user**  
  `GET /api/api/manage-user`  
- **Create a new user**  
  `POST /api/api/manage-user`  
- **Update a user**  
  `PUT /api/api/manage-user{id}`  
- **Delete a user**  
  `DELETE /api/api/manage-user[id}`

