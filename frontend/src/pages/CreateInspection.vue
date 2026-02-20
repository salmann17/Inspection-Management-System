<template>
  <div class="page">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <span>Quality &amp; HSE</span>
      <span class="sep">›</span>
      <span>Inspection</span>
      <span class="sep">›</span>
      <span class="active">Create Inspection</span>
    </nav>

    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <button class="btn-back" @click="$router.push('/inspections')">← Back</button>
        <h1 class="page-title">Create Inspection</h1>
      </div>
    </div>

    <!-- Content placeholder -->
    <div class="card">
      <p class="placeholder">Form coming soon.</p>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 28px 24px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #94a3b8;
  margin-bottom: 16px;
}

.breadcrumb .sep {
  color: #cbd5e1;
}

.breadcrumb .active {
  color: #1d4ed8;
  font-weight: 500;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.btn-back {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 6px 14px;
  color: #475569;
  font-weight: 500;
  transition: border-color 0.15s, color 0.15s;
}

.btn-back:hover {
  border-color: #1d4ed8;
  color: #1d4ed8;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
}

.card {
  background: #fff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  padding: 40px 24px;
}

.placeholder {
  text-align: center;
  color: #94a3b8;
  font-size: 14px;
}
</style>
