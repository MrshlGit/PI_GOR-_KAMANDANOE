<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Tagihan - GOR Kamandanoe</title>
  <link rel="stylesheet" href="{{ asset('css/DaftaraTagihan.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <div class="app-container">
    
    <!-- Sidebar Left -->
    <aside class="sidebar">
      <div class="brand">
        <h2>GOR<br>Kamandanoe</h2>
      </div>

      <nav class="nav-menu">
        <a href="#" class="nav-item">
          <i data-lucide="house"></i>
          <span>Home</span>
        </a>
        <a href="#" class="nav-item">
          <i data-lucide="trophy"></i>
          <span>Info Turnamen</span>
        </a>
        <a href="#" class="nav-item">
          <i data-lucide="info"></i>
          <span>Layanan Informasi</span>
        </a>
        <a href="#" class="nav-item">
          <i data-lucide="shopping-cart"></i>
          <span>Pemesanan</span>
        </a>
        <a href="#" class="nav-item active">
          <i data-lucide="hand-coins"></i>
          <span>Transaksi</span>
        </a>
      </nav>

      <div class="logout-wrapper">
        <a href="#" class="logout-btn">
          <i data-lucide="log-out"></i>
          <span>Logout</span>
        </a>
      </div>
    </aside>

    <!-- Main Content Right -->
    <main class="main-content">
      
      <!-- Top Header -->
      <header class="top-bar">
        <div class="search-box">
          <i data-lucide="search"></i>
          <input type="text" placeholder="Cari lapangan">
        </div>
        <div class="user-profile-wrapper">
          <button class="bell-btn"><i data-lucide="bell"></i></button>
          <div class="user-card">
            <i data-lucide="circle-user" class="user-icon"></i>
            <div class="user-name">
              <strong>Isnan</strong>
              <span>User</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <section class="content-body">
        
        <!-- Header Banner Card -->
        <div class="header-card">
          <h1>Daftar Tagihan</h1>
          <p>Sebelum melakukan pembayaran mohon dicek semua barang yang telah dipesan sesuai dengan deskripsi pembayaran.!!</p>
        </div>

        <!-- Grid Layout: Customer Info & Order Table -->
        <div class="data-grid">
          
          <!-- Customer Card -->
          <div class="customer-card">
            <div class="card-title-bar">
              <i data-lucide="users"></i>
              <span>Customer</span>
            </div>
            <div class="card-body">
              <div class="form-row">
                <label>Nama Custemer</label>
                <input type="text" value="{{ $customer['nama'] }}" readonly class="input-white">
              </div>
              <div class="form-row">
                <label>No Telepon</label>
                <input type="text" value="{{ $customer['telepon'] }}" readonly class="input-gray">
              </div>
              <div class="form-row">
                <label>Email</label>
                <input type="text" value="{{ $customer['email'] }}" readonly class="input-gray">
              </div>
            </div>
          </div>

          <!-- Order Table Card -->
          <div class="table-card">
            <table>
              <thead>
                <tr>
                  <th>NAMA</th>
                  <th>JENIS</th>
                  <th>JUMLAH</th>
                  <th>DURASI</th>
                  <th>HARGA</th>
                </tr>
              </thead>
              <tbody>
                @foreach($tagihans as $item)
                  <tr>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['jenis'] }}</td>
                    <td>{{ $item['jumlah'] }}</td>
                    <td>{{ $item['durasi'] }}</td>
                    <td>{{ $item['harga'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>

        <!-- Action Buttons -->
        <div class="action-footer">
          <button class="btn btn-confirm">
            Konfirmasi
            <i data-lucide="check-circle-2"></i>
          </button>
          <button class="btn btn-cancel">
            <i data-lucide="trash-2"></i>
            Batal
          </button>
        </div>

      </section>

    </main>

  </div>

  <script>
    // Initialize Lucide Icons
    lucide.createIcons();
  </script>
</body>
</html>