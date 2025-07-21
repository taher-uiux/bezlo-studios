# Bezlo Studio - Deployment Guide

## 🚀 Quick Start Deployment

### 1. Choose Your Hosting Provider

**Recommended Hosting Providers:**
- **Shared Hosting**: Hostinger, SiteGround, Bluehost
- **VPS**: DigitalOcean, Linode, Vultr
- **Cloud**: AWS, Google Cloud, Azure

**Minimum Requirements:**
- PHP 7.4 or higher
- Apache or Nginx web server
- SSL certificate support
- Email functionality

### 2. Domain Setup

1. **Register Domain** (if not already done)
   - Recommended: GoDaddy, Namecheap, Google Domains
   - Choose: `bezlostudio.com` or similar

2. **Configure DNS**
   - Point A record to your hosting IP
   - Set up CNAME for www subdomain
   - Configure MX records for email

### 3. File Upload

#### Option A: FTP/SFTP Upload
```bash
# Using FileZilla or similar FTP client
1. Connect to your hosting server
2. Upload all files to public_html/ or www/
3. Ensure .htaccess is uploaded
4. Set file permissions:
   - Folders: 755
   - Files: 644
   - PHP files: 644
```

#### Option B: cPanel File Manager
1. Login to cPanel
2. Open File Manager
3. Navigate to public_html/
4. Upload all files
5. Set permissions as above

### 4. Database Setup (if needed)

**For this static site, no database is required.**

### 5. Email Configuration

1. **Configure Email Settings**
   - Update `send_email.php` with your email
   - Test email functionality
   - Set up SPF/DKIM records

2. **Email Testing**
   ```php
   // In send_email.php, update this line:
   $to = "your-email@domain.com";
   ```

### 6. SSL Certificate

**Enable HTTPS:**
1. Most hosting providers offer free Let's Encrypt SSL
2. Enable SSL in hosting control panel
3. Update .htaccess to force HTTPS (uncomment lines)

### 7. Testing Checklist

#### Functionality Tests
- [ ] Homepage loads correctly
- [ ] All navigation links work
- [ ] Contact form submits successfully
- [ ] CAPTCHA works properly
- [ ] Images load properly
- [ ] Mobile responsiveness

#### Performance Tests
- [ ] PageSpeed Insights score > 90
- [ ] Mobile performance > 90
- [ ] Core Web Vitals pass
- [ ] Images optimized

#### SEO Tests
- [ ] Meta tags display correctly
- [ ] Structured data validates
- [ ] Sitemap accessible
- [ ] robots.txt working

### 8. Post-Deployment Setup

#### Google Analytics
1. Create Google Analytics account
2. Add tracking code to all pages
3. Set up goals and conversions

#### Google Search Console
1. Add and verify your domain
2. Submit sitemap.xml
3. Monitor for crawl errors

#### Performance Monitoring
1. Set up UptimeRobot for monitoring
2. Configure error notifications
3. Monitor page load times

## 🔧 Advanced Configuration

### Apache Configuration (.htaccess)
The .htaccess file is already configured with:
- URL rewriting
- Security headers
- Compression
- Caching rules
- Error pages

### PHP Configuration
Ensure these PHP settings:
```ini
max_execution_time = 300
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
```

### Security Hardening
1. **File Permissions**
   ```bash
   find . -type d -exec chmod 755 {} \;
   find . -type f -exec chmod 644 {} \;
   ```

2. **Protect Sensitive Files**
   - .htaccess already configured
   - PHP files in assets/ blocked
   - Sensitive files protected

## 📊 Monitoring & Maintenance

### Regular Tasks
- [ ] Monitor website uptime
- [ ] Check for broken links
- [ ] Update content regularly
- [ ] Monitor security threats
- [ ] Backup files monthly

### Performance Monitoring
- [ ] Weekly PageSpeed tests
- [ ] Monitor Core Web Vitals
- [ ] Check mobile performance
- [ ] Monitor loading times

### Security Monitoring
- [ ] Regular security scans
- [ ] Monitor error logs
- [ ] Update SSL certificates
- [ ] Check for vulnerabilities

## 🆘 Troubleshooting

### Common Issues

**1. Contact Form Not Working**
- Check PHP mail() function
- Verify email configuration
- Test CAPTCHA functionality
- Check server error logs

**2. Images Not Loading**
- Verify file permissions
- Check image paths
- Ensure WebP support
- Test with different browsers

**3. SSL Issues**
- Verify certificate installation
- Check .htaccess redirects
- Test mixed content warnings
- Update internal links

**4. Performance Issues**
- Optimize images further
- Enable server-side caching
- Minify CSS/JS files
- Use CDN for assets

## 📞 Support

**For technical issues:**
- Check hosting provider support
- Review error logs
- Test in different browsers
- Contact Bezlo Studio for assistance

**Emergency Contacts:**
- Hosting Provider Support
- Domain Registrar Support
- Bezlo Studio: bezlostudio@gmail.com

## 🎯 Success Metrics

### SEO Goals
- Google ranking for target keywords
- Organic traffic growth
- Low bounce rate (< 50%)
- High page load speed (> 90)

### Business Goals
- Contact form submissions
- Portfolio view engagement
- Social media traffic
- Client inquiries

---

**Deployment Status:** ✅ Ready
**Last Updated:** December 19, 2024
**Version:** 1.0 