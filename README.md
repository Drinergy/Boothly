# Modern Photobooth Web App

A beautiful, modern photobooth web application built with Laravel that allows users to capture multiple photos with customizable timer settings and create Instax-style collages for download.

## Features

- 📸 **Live Camera Feed**: Real-time camera preview with high-quality capture
- 🔢 **Customizable Photo Count**: Select 1-6 photos per session
- ⏱️ **Adjustable Timer**: Set countdown timer from 1-10 seconds
- 🖼️ **Instax-Style Collages**: Automatic collage creation with vintage photo frame styling
- 📱 **Responsive Design**: Works on desktop and mobile devices
- 💾 **Instant Download**: Download your collage immediately after creation
- 🎨 **Modern UI**: Beautiful gradient design with smooth animations

## Requirements

- PHP 7.4 or higher
- Composer
- Web server (Apache/Nginx) or Laravel's built-in server
- Modern web browser with camera access
- GD extension for PHP (for image processing)

## Installation

1. **Clone or download the project**
   ```bash
   # If you haven't already created the project
   composer create-project laravel/laravel photobooth
   cd photobooth
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Create storage link**
   ```bash
   php artisan storage:link
   ```

5. **Create required directories** (if not already created)
   ```bash
   mkdir -p storage/app/public/photos
   mkdir -p storage/app/public/collages
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

7. **Access the application**
   Open your browser and go to `http://localhost:8000`

## Usage

1. **Allow Camera Access**: When prompted, allow the browser to access your camera
2. **Select Photo Count**: Choose how many photos you want to take (1-6)
3. **Set Timer**: Adjust the countdown timer duration (1-10 seconds)
4. **Start Session**: Click "Start Photo Session" to begin
5. **Pose & Capture**: Get ready during the countdown, photo will be taken automatically
6. **Repeat**: Continue until all photos are captured
7. **Create Collage**: Click "Create & Download" to generate your Instax-style collage
8. **Download**: Your collage will automatically download

## Technical Details

### Backend (Laravel)
- **PhotoboothController**: Handles photo capture, storage, and collage creation
- **Image Processing**: Uses PHP's GD library for collage generation
- **File Storage**: Photos and collages stored in `storage/app/public/`
- **Routes**: RESTful API endpoints for capture, collage creation, and download

### Frontend (Vanilla JavaScript)
- **Camera API**: Uses `navigator.mediaDevices.getUserMedia()` for camera access
- **Canvas Processing**: Captures video frames to canvas for photo processing
- **AJAX Requests**: Communicates with Laravel backend via fetch API
- **Responsive Design**: CSS Grid and Flexbox for modern layouts

### Collage Features
- **Instax Styling**: White borders with extra space at bottom
- **Photo Numbering**: Each photo numbered in sequence
- **Shadows**: Subtle drop shadows for depth
- **Title & Date**: Automatic title and timestamp
- **Flexible Layout**: Adapts to different photo counts

## File Structure

```
photobooth/
├── app/Http/Controllers/PhotoboothController.php
├── resources/views/photobooth/index.blade.php
├── routes/web.php
├── storage/app/public/
│   ├── photos/          # Individual captured photos
│   └── collages/        # Generated collages
└── public/storage/      # Symlink to storage/app/public
```

## Customization

### Styling
- Edit `resources/views/photobooth/index.blade.php` to modify the UI
- Adjust CSS variables for colors, spacing, and animations
- Modify the gradient backgrounds and button styles

### Collage Layout
- Edit `PhotoboothController::generateInstaxCollage()` method
- Adjust photo dimensions, borders, and spacing
- Customize title, fonts, and positioning

### Photo Limits
- Modify validation rules in controller methods
- Update frontend photo count selector options
- Adjust grid layouts for different photo counts

## Browser Compatibility

- Chrome 53+
- Firefox 36+
- Safari 11+
- Edge 12+

**Note**: Camera access requires HTTPS in production environments.

## Troubleshooting

### Camera Not Working
- Ensure browser has camera permissions
- Check if camera is being used by another application
- Try refreshing the page
- Use HTTPS in production

### Photos Not Saving
- Check storage directory permissions
- Ensure `storage:link` command was run
- Verify GD extension is installed

### Collage Generation Issues
- Confirm GD extension is enabled
- Check PHP memory limits for large images
- Verify write permissions on storage directories

## Security Considerations

- Input validation on all photo uploads
- File type restrictions (PNG only)
- Session-based photo organization
- Automatic cleanup of old files (implement as needed)

## License

This project is open-source and available under the MIT License.

## Contributing

Feel free to submit issues, fork the repository, and create pull requests for any improvements.

---

**Enjoy creating memories with your modern photobooth! 📸✨**
