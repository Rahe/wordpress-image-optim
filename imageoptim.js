var imagemin = require('imagemin');
var imageminMozjpeg = require('imagemin-mozjpeg');
var imageminPngquant = require('imagemin-pngquant');
var imageminGifsicle = require('imagemin-gifsicle');

imagemin([process.argv[2]],
	process.argv[3],
	{
		plugins: [
			imageminMozjpeg({quality: 70}),
			imageminPngquant({quality: 70}),
			imageminGifsicle({optimizationLevel: 3}),
		]
	}).catch(error => {
	console.error(error);
} );
