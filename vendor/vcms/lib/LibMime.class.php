<?php
/*
This file is part of VCMS.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

namespace vcms;

class LibMime{
	function detectMime($filename){
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$mime = '';

		switch ($extension) {
			case 'ai': $mime='application/postscript'; break;
			case 'aif': $mime='audio/x-aiff'; break;
			case 'aifc': $mime='audio/x-aiff'; break;
			case 'aiff': $mime='audio/x-aiff'; break;
			case 'asc': $mime='text/plain'; break;
			case 'asf': $mime='video/x-ms-asf'; break;
			case 'asx': $mime='video/x-ms-asf'; break;
			case 'au': $mime='audio/basic'; break;
			case 'avi': $mime='video/x-msvideo'; break;
			case 'bcpio': $mime='application/x-bcpio'; break;
			case 'bin': $mime='application/octet-stream'; break;
			case 'bmp': $mime='image/bmp'; break;
			case 'cdf': $mime='application/x-netcdf'; break;
			case 'class': $mime='application/octet-stream'; break;
			case 'cpio': $mime='application/x-cpio'; break;
			case 'cpt': $mime='application/mac-compactpro'; break;
			case 'csh': $mime='application/x-csh'; break;
			case 'css': $mime='text/css'; break;
			case 'dcr': $mime='application/x-director'; break;
			case 'dir': $mime='application/x-director'; break;
			case 'djv': $mime='image/vnd.djvu'; break;
			case 'djvu': $mime='image/vnd.djvu'; break;
			case 'dll': $mime='application/octet-stream'; break;
			case 'dms': $mime='application/octet-stream'; break;
			case 'doc': $mime='application/msword'; break;
			case 'dvi': $mime='application/x-dvi'; break;
			case 'dxr': $mime='application/x-director'; break;
			case 'eps': $mime='application/postscript'; break;
			case 'etx': $mime='text/x-setext'; break;
			case 'exe': $mime='application/octet-stream'; break;
			case 'ez': $mime='application/andrew-inset'; break;
			case 'gif': $mime='image/gif'; break;
			case 'gtar': $mime='application/x-gtar'; break;
			case 'hdf': $mime='application/x-hdf'; break;
			case 'hqx': $mime='application/mac-binhex40'; break;
			case 'htm': $mime='text/html'; break;
			case 'html': $mime='text/html'; break;
			case 'ice': $mime='x-conference/x-cooltalk'; break;
			case 'ief': $mime='image/ief'; break;
			case 'iges': $mime='model/iges'; break;
			case 'igs': $mime='model/iges'; break;
			case 'jpe': $mime='image/jpeg'; break;
			case 'jpeg': $mime='image/jpeg'; break;
			case 'jpg': $mime='image/jpeg'; break;
			case 'js': $mime='application/x-javascript'; break;
			case 'kar': $mime='audio/midi'; break;
			case 'latex': $mime='application/x-latex'; break;
			case 'lha': $mime='application/octet-stream'; break;
			case 'lzh': $mime='application/octet-stream'; break;
			case 'm3u': $mime='audio/x-mpegurl'; break;
			case 'man': $mime='application/x-troff-man'; break;
			case 'me': $mime='application/x-troff-me'; break;
			case 'mesh': $mime='model/mesh'; break;
			case 'mid': $mime='audio/midi'; break;
			case 'midi': $mime='audio/midi'; break;
			case 'mov': $mime='video/quicktime'; break;
			case 'movie': $mime='video/x-sgi-movie'; break;
			case 'mp2': $mime='audio/mpeg'; break;
			case 'mp3': $mime='audio/mpeg'; break;
			case 'mpe': $mime='video/mpeg'; break;
			case 'mpeg': $mime='video/mpeg'; break;
			case 'mpg': $mime='video/mpeg'; break;
			case 'mpga': $mime='audio/mpeg'; break;
			case 'ms': $mime='application/x-troff-ms'; break;
			case 'msh': $mime='model/mesh'; break;
			case 'mxu': $mime='video/vnd.mpegurl'; break;
			case 'nc': $mime='application/x-netcdf'; break;
			case 'oda': $mime='application/oda'; break;
			case 'pbm': $mime='image/x-portable-bitmap'; break;
			case 'pdb': $mime='chemical/x-pdb'; break;
			case 'pdf': $mime='application/pdf'; break;
			case 'pgm': $mime='image/x-portable-graymap'; break;
			case 'pgn': $mime='application/x-chess-pgn'; break;
			case 'png': $mime='image/png'; break;
			case 'pnm': $mime='image/x-portable-anymap'; break;
			case 'ppm': $mime='image/x-portable-pixmap'; break;
			case 'ppt': $mime='application/vnd.ms-powerpoint'; break;
			case 'ps': $mime='application/postscript'; break;
			case 'qt': $mime='video/quicktime'; break;
			case 'ra': $mime='audio/x-realaudio'; break;
			case 'ram': $mime='audio/x-pn-realaudio'; break;
			case 'ras': $mime='image/x-cmu-raster'; break;
			case 'rgb': $mime='image/x-rgb'; break;
			case 'rm': $mime='audio/x-pn-realaudio'; break;
			case 'roff': $mime='application/x-troff'; break;
			case 'rpm': $mime='audio/x-pn-realaudio-plugin'; break;
			case 'rtf': $mime='text/rtf'; break;
			case 'rtx': $mime='text/richtext'; break;
			case 'sgm': $mime='text/sgml'; break;
			case 'sgml': $mime='text/sgml'; break;
			case 'sh': $mime='application/x-sh'; break;
			case 'shar': $mime='application/x-shar'; break;
			case 'silo': $mime='model/mesh'; break;
			case 'sit': $mime='application/x-stuffit'; break;
			case 'skd': $mime='application/x-koan'; break;
			case 'skm': $mime='application/x-koan'; break;
			case 'skp': $mime='application/x-koan'; break;
			case 'skt': $mime='application/x-koan'; break;
			case 'smi': $mime='application/smil'; break;
			case 'smil': $mime='application/smil'; break;
			case 'snd': $mime='audio/basic'; break;
			case 'so': $mime='application/octet-stream'; break;
			case 'spl': $mime='application/x-futuresplash'; break;
			case 'src': $mime='application/x-wais-source'; break;
			case 'sv4cpio': $mime='application/x-sv4cpio'; break;
			case 'sv4crc': $mime='application/x-sv4crc'; break;
			case 'swf': $mime='application/x-shockwave-flash'; break;
			case 't': $mime='application/x-troff'; break;
			case 'tar': $mime='application/x-tar'; break;
			case 'tcl': $mime='application/x-tcl'; break;
			case 'tex': $mime='application/x-tex'; break;
			case 'texi': $mime='application/x-texinfo'; break;
			case 'texinfo': $mime='application/x-texinfo'; break;
			case 'tif': $mime='image/tiff'; break;
			case 'tiff': $mime='image/tiff'; break;
			case 'tr': $mime='application/x-troff'; break;
			case 'tsv': $mime='text/tab-separated-values'; break;
			case 'txt': $mime='text/plain'; break;
			case 'ustar': $mime='application/x-ustar'; break;
			case 'vcd': $mime='application/x-cdlink'; break;
			case 'vrml': $mime='model/vrml'; break;
			case 'wav': $mime='audio/x-wav'; break;
			case 'wbmp': $mime='image/vnd.wap.wbmp'; break;
			case 'wbxml': $mime='application/vnd.wap.wbxml'; break;
			case 'wm': $mime='video/x-ms-wm'; break;
			case 'wml': $mime='text/vnd.wap.wml'; break;
			case 'wmlc': $mime='application/vnd.wap.wmlc'; break;
			case 'wmls': $mime='text/vnd.wap.wmlscript'; break;
			case 'wmlsc': $mime='application/vnd.wap.wmlscriptc'; break;
			case 'wmv': $mime='video/x-ms-wmv'; break;
			case 'wrl': $mime='model/vrml'; break;
			case 'wvx': $mime='video/x-ms-wvx'; break;
			case 'xbm': $mime='image/x-xbitmap'; break;
			case 'xht': $mime='application/xhtml+xml'; break;
			case 'xhtml': $mime='application/xhtml+xml'; break;
			case 'xls': $mime='application/vnd.ms-excel'; break;
			case 'xml': $mime='text/xml'; break;
			case 'xpm': $mime='image/x-xpixmap'; break;
			case 'xsl': $mime='text/xml'; break;
			case 'xwd': $mime='image/x-xwindowdump'; break;
			case 'xyz': $mime='chemical/x-xyz'; break;
			case 'zip': $mime='application/zip'; break;
			default: $mime='application/octet-stream'; break;
		}

		return $mime;
	}
}