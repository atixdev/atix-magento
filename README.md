# Atix Payment Gateway para Magento

![Magento](https://img.shields.io/badge/Magento-2.4+-FF6C37?style=flat-square&logo=magento)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)

Módulo de integración con la pasarela de pagos Atix, desarrollada por Atix.

---

## 📋 Tabla de Contenidos

- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Configuración en la Plataforma Atix](#-configuración-en-la-plataforma-atix)
- [Solución de Problemas](#-solución-de-problemas)
- [Soporte](#-soporte)

---

## 🔧 Requisitos del Sistema

Antes de instalar el módulo, asegúrese de cumplir con los siguientes requisitos:

| Componente | Versión Mínima |
|------------|----------------|
| Magento    | 2.4+          |
| PHP        | 8.2+          |

**Adicionales:**
- Acceso SSH o SFTP al servidor
- Permisos de escritura en el directorio de Magento

---

## 📦 Instalación

### Paso 1: Descargar el Plugin

Descargue el archivo desde el repositorio oficial.

### Paso 2: Subir el Plugin al Servidor

Conéctese a su servidor mediante **SSH** o **SFTP** y suba el archivo zip.

### Paso 3: Descomprimir e Instalar

```bash
# Navegue al directorio raíz de Magento
cd /ruta/a/magento

# Cree el directorio app/code si no existe
mkdir -p app/code

# Descomprima el plugin
unzip atix-payment-gateway.zip -d app/code/
```

### Paso 4: Compilar y Activar el Módulo

Ejecute los siguientes comandos en orden:

```bash
# 1. Actualizar configuración de módulos
php bin/magento setup:upgrade

# 2. Compilar dependencias
php bin/magento setup:di:compile

# 3. Desplegar contenido estático
php bin/magento setup:static-content:deploy -f

# 4. Verificar estado del módulo
php bin/magento module:status Atix_PaymentGateway

# 5. Habilitar el módulo (si está deshabilitado)
php bin/magento module:enable Atix_PaymentGateway --clear-static-content

# 6. Limpiar caché
php bin/magento cache:clean
php bin/magento cache:flush
```

### Paso 5: Verificar la Instalación

1. Acceda al panel de administración de Magento
2. Navegue a: **Stores → Configuration → Sales → Payment Methods**
3. En la sección **Other Payment Methods**, busque **"Atix Payment Gateway"**
4. Si el módulo aparece en la lista, la instalación fue exitosa ✅

---

## ⚙️ Configuración

### Activar el Método de Pago

1. En el panel de administración, vaya a:
   ```
   Stores → Configuration → Sales → Payment Methods → Other Payment Methods
   ```

2. Localice **"Pasarela de pagos Atix"** y expanda la sección

3. Configure los siguientes campos:

| Campo | Descripción | Opciones |
|-------|-------------|----------|
| **Enabled** | Habilitar método de pago | `Yes` / `No` |
| **API Key** | Clave de autenticación | (desde plataforma Atix) |
| **Debug Mode** | Modo de operación | `Yes` (Pruebas) / `No` (Producción) |
| **Title** | Nombre visible para clientes | Ej: "Pago con tarjeta" |

> 💡 **Nota**: Si no aparece la opción "Enabled", marque primero "Use system value"

4. Haga clic en **"Save Config"**

5. Limpie el caché nuevamente:
   ```bash
   php bin/magento cache:flush
   ```

---

## 🌐 Configuración en la Plataforma Atix

### Obtener el API Key

1. Inicie sesión en su cuenta de [Atix Payment Gateway](https://dashboard.atix.com.pe/)
2. Navegue a:
   ```
   Mi Cuenta → Datos de la cuenta → Configuración
   ```
3. Localice la sección **API Key**
4. Copie la clave y péguela en la configuración de Magento dependiendo si es **Soles** o **Dólares**

### Configurar URLs de Redirección

⚠️ **Las URLs de redirección son críticas para el correcto funcionamiento del módulo.**

1. En la plataforma Atix, vaya a:
   ```
   Mi Cuenta → Datos de la cuenta → Configuración → URLs de respuesta
   ```

2. Configure la siguiente URL (reemplace `mitienda.com` con su dominio):

   **URL de confirmación** (transacciones aprobadas y rechazadas):
   ```
   https://mitienda.com/atixpaymentgateway/payment/confirmation?tk={{{tokenid}}}
   ```

3. Haga clic en **"Guardar configuración"**

> ⚠️ **Importante**: Verifique que la URL base coincida **exactamente** con la URL de su tienda Magento. Una discrepancia en el dominio causará errores en la verificación de pagos.

---

## 🔍 Solución de Problemas

### El módulo no aparece en Payment Methods

**Soluciones:**
- Verifique que el módulo esté habilitado:
  ```bash
  php bin/magento module:status Atix_PaymentGateway
  ```
- Limpie el caché completamente:
  ```bash
  php bin/magento cache:flush
  ```
- Revise los logs en `var/log/` para errores específicos

### Errores durante la compilación

**Soluciones:**
- Verifique los permisos de escritura en los directorios de Magento:
  ```bash
  chmod -R 755 var/ pub/ generated/
  ```
- Asegúrese de cumplir con los requisitos de PHP y Magento
- Ejecute nuevamente:
  ```bash
  php bin/magento setup:upgrade
  ```

### Los pagos no se confirman correctamente

**Verificar:**
- ✅ URLs de redirección correctamente configuradas en Atix
- ✅ El dominio en las URLs coincide con su tienda
- ✅ El API Key es correcto
- ✅ El modo (prueba/producción) es el apropiado

### Modo de prueba vs Producción

| Modo | Debug Mode | Uso |
|------|------------|-----|
| **Prueba** | `Yes` | Use credenciales de prueba proporcionadas por Atix |
| **Producción** | `No` | Use su API Key real. Realice transacciones de prueba antes del lanzamiento |

---

## 📞 Soporte

Para asistencia técnica o consultas adicionales:

- **Email**: soporteti@atix.com.pe
- **Documentación**: https://docs.atix.com.pe
- **Desarrollador**: Atix

---

**Versión del documento**: 1.0  
**Última actualización**: Enero 2026

---

<p align="center">Desarrollado con ❤️ por Atix</p>