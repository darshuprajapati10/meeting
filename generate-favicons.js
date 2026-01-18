/**
 * Script to generate favicon files from SVG logo
 * 
 * Prerequisites:
 * npm install sharp --save-dev
 * 
 * Usage:
 * node generate-favicons.js
 */

const fs = require('fs');
const path = require('path');

// Check if sharp is available
let sharp;
try {
    sharp = require('sharp');
} catch (e) {
    console.error('Error: sharp module not found. Install it with: npm install sharp --save-dev');
    process.exit(1);
}

const publicDir = path.join(__dirname, 'public');
const svgPath = path.join(publicDir, 'favicon.svg');

// Check if SVG exists
if (!fs.existsSync(svgPath)) {
    console.error(`Error: ${svgPath} not found!`);
    process.exit(1);
}

const sizes = [
    { name: 'favicon-16x16.png', size: 16 },
    { name: 'favicon-32x32.png', size: 32 },
    { name: 'apple-touch-icon.png', size: 180 },
    { name: 'android-chrome-192x192.png', size: 192 },
    { name: 'android-chrome-512x512.png', size: 512 },
];

async function generateFavicons() {
    console.log('Generating favicon files from SVG...\n');

    for (const { name, size } of sizes) {
        try {
            const outputPath = path.join(publicDir, name);
            await sharp(svgPath)
                .resize(size, size, {
                    fit: 'contain',
                    background: { r: 255, g: 255, b: 255, alpha: 0 }
                })
                .png()
                .toFile(outputPath);
            
            console.log(`✅ Generated ${name} (${size}x${size})`);
        } catch (error) {
            console.error(`❌ Error generating ${name}:`, error.message);
        }
    }

    // Generate favicon.ico from 16x16 and 32x32
    try {
        const favicon16 = path.join(publicDir, 'favicon-16x16.png');
        const favicon32 = path.join(publicDir, 'favicon-32x32.png');
        
        if (fs.existsSync(favicon16) && fs.existsSync(favicon32)) {
            // Note: Creating a proper multi-size ICO requires a specialized library
            // For now, we'll use the 32x32 version as favicon.ico
            await sharp(favicon32)
                .resize(32, 32)
                .png()
                .toFile(path.join(publicDir, 'favicon-temp.ico'));
            
            // Copy as favicon.ico (this is a PNG masquerading as ICO, but works)
            // For a true ICO file, you'd need a library like 'to-ico'
            fs.copyFileSync(favicon32, path.join(publicDir, 'favicon.ico'));
            console.log('✅ Generated favicon.ico (using 32x32 version)');
        }
    } catch (error) {
        console.error('❌ Error generating favicon.ico:', error.message);
    }

    console.log('\n✨ Favicon generation complete!');
    console.log('\nNote: For a proper multi-size ICO file, consider using:');
    console.log('   npm install to-ico --save-dev');
    console.log('   Or use an online tool: https://realfavicongenerator.net/');
}

generateFavicons().catch(console.error);

