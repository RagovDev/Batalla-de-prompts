// server/server.js
require('dotenv').config();

const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs').promises;
const cors = require('cors');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken'); 

const app = express();
const whitelist = process.env.ALLOWED_ORIGINS ? process.env.ALLOWED_ORIGINS.split(',') : [];

const corsOptions = {
  origin: function (origin, callback) {
    // Permite peticiones sin 'origin' (como las de Postman o apps móviles)
    if (!origin || whitelist.indexOf(origin) !== -1) {
      callback(null, true);
    } else {
      callback(new Error('No permitido por CORS'));
    }
  },
  optionsSuccessStatus: 200
};

app.use(express.json());
app.use(cors(corsOptions));

const UPLOAD_DIR = path.join(__dirname, 'uploads');
const DB_FILE = path.join(__dirname, 'db.json');
const JWT_SECRET = '-UT&7?$1,zmZksN[P(uv~c(ktsfXZ8/Z';


/**
 * MIDDLEWARE DE AUTENTICACIÓN
 * Protege las rutas verificando el token JWT
 */
const authMiddleware = (req, res, next) => {
  // 1. Obtener el token del encabezado 'Authorization'
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1]; // Formato: "Bearer TOKEN"

  if (token == null) {
    // Si no hay token, no hay acceso
    return res.sendStatus(401); // 401 Unauthorized
  }

  // 2. Verificar que el token sea válido
  jwt.verify(token, JWT_SECRET, (err, user) => {
    if (err) {
      // Si el token es inválido o ha expirado
      return res.sendStatus(403); // 403 Forbidden
    }

    // 3. Si es válido, guardar los datos del usuario en el objeto `req`
    req.user = user; // 'user' son los datos que pusimos en el token (id y name)

    // 4. Dejar pasar la petición a la siguiente función (la lógica de la ruta)
    next();
  });
};

// Init: asegúrate de que uploads y db existan
(async function init() {
  try {
    await fs.mkdir(UPLOAD_DIR, { recursive: true });
    try {
      await fs.access(DB_FILE);
    } catch {
      await fs.writeFile(DB_FILE, JSON.stringify({ images: [], votes: [] }, null, 2));
    }
  } catch (err) {
    console.error('Error init:', err);
    process.exit(1);
  }
})();

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, UPLOAD_DIR),
  filename: (req, file, cb) => {
    const unique = Date.now() + '-' + Math.random().toString(36).slice(2,9);
    cb(null, unique + path.extname(file.originalname));
  }
});
const upload = multer({ storage });

async function readDB() {
  const content = await fs.readFile(DB_FILE, 'utf8');
  return JSON.parse(content || '{}');
}
async function writeDB(db) {
  await fs.writeFile(DB_FILE, JSON.stringify(db, null, 2));
}

app.use('/uploads', express.static(UPLOAD_DIR));


/**
 * Subir imagen a una ronda:
 * POST /api/rondas/:ronda/upload
 * FormData: image (file), name (participant name)
 * 
 * Migracion OK
 */
app.post('/api/rondas/:ronda/upload', authMiddleware, upload.single('image'), async (req, res) => {
  const { ronda } = req.params;
  const name = req.user.name; // <-- AHORA se toma del token verificado (seguro)

  if (!req.file) return res.status(400).json({ message: 'No se recibió archivo' });

  // Lógica para evitar que un mismo usuario suba más de una foto por ronda
  const db = await readDB();
  const existingImage = db.images.find(img => img.name === name && img.ronda === Number(ronda));
  if (existingImage) {
    return res.status(400).json({ message: 'Ya has subido una imagen para esta ronda.' });
  }

  const image = {
    id: Date.now().toString() + '-' + Math.random().toString(36).substr(2, 9),
    name: name, // Usamos el nombre seguro
    filename: req.file.filename,
    url: `/uploads/${req.file.filename}`,
    ronda: Number(ronda),
    votes: 0
  };
  db.images.push(image);
  await writeDB(db);
  res.json(image);
});


/**
 * Borrar una imagen:
 * DELETE /api/images/:id
 * - Borra la imagen de la DB
 * - Borra el archivo físico del disco
 * - Borra los votos asociados
 * 
 * Migracion OK
 */
app.delete('/api/images/:id', authMiddleware, async (req, res) => {
  const { id } = req.params;
  const db = await readDB();

  const imageIndex = db.images.findIndex(i => i.id === id);
  if (imageIndex === -1) {
    return res.status(404).json({ message: 'Imagen no encontrada' });
  }

  const image = db.images[imageIndex];

  // ¡VERIFICACIÓN DE AUTORIZACIÓN!
  // Comprobamos que el nombre en la imagen coincida con el nombre en el token
  if (image.name !== req.user.name) {
    return res.status(403).json({ message: 'No tienes permiso para borrar esta imagen.' }); // 403 Forbidden
  }

  try {
    await fs.unlink(path.join(UPLOAD_DIR, image.filename));
    db.images.splice(imageIndex, 1);
    db.votes = db.votes.filter(v => v.imageId !== id);
    await writeDB(db);
    res.status(200).json({ success: true, message: 'Imagen borrada correctamente' });
  } catch (err) {
    console.error('Error al borrar la imagen:', err);
    res.status(500).json({ message: 'Error interno del servidor.' });
  }
});


/**
 * Listar imágenes de una ronda
 * GET /api/rondas/:ronda/images
 * 
 * Migracion OK
 */
app.get('/api/rondas/:ronda/images', async (req, res) => {
  const db = await readDB();
  const ronda = Number(req.params.ronda);
  const imgs = db.images.filter(i => i.ronda === ronda);
  res.json(imgs);
});


/**
 * Votar por una imagen
 * POST /api/vote
 * body: { imageId, voterName }
 * - impide doble voto por misma imagen+voterName
 * 
 * Migracion OK
 */
app.post('/api/vote', async (req, res) => {
  const { imageId, voterName } = req.body;
  if (!imageId || !voterName) return res.status(400).json({ message: 'imageId y voterName requeridos' });

  const db = await readDB();
  const image = db.images.find(i => i.id === imageId);
  if (!image) return res.status(404).json({ message: 'Imagen no encontrada' });

  const exists = db.votes.find(v => v.imageId === imageId && v.voterName === voterName);
  if (exists) return res.status(400).json({ message: 'Ya votaste por esta imagen' });

  const vote = {
    id: Date.now().toString() + '-' + Math.random().toString(36).substr(2,9),
    imageId,
    voterName,
    ronda: image.ronda,
    ts: Date.now()
  };
  db.votes.push(vote);
  image.votes = (image.votes || 0) + 1;
  await writeDB(db);
  res.json({ success: true, votes: image.votes });
});


/**
 * Computar puntajes con la nueva mecánica:
 * - Votos ponderados por ronda (R1x1, R2x2, R3x3, R4x4).
 * - Puntuación por ranking: 1ro: 10, 2do: 8, 3ro: 6, resto: 4.
 */
function computeScores(db) {
  const rounds = [1, 2, 3, 4];
  const participants = {};

  const participantNames = [...new Set((db.images || []).map(img => img.name))];

  participantNames.forEach(name => {
    participants[name] = {
      name: name,
      puntos: [0, 0, 0, 0],
      total: 0,
      roundData: {
        1: { votes: 0, points: 0, finalScore: 0 },
        2: { votes: 0, points: 0, finalScore: 0 },
        3: { votes: 0, points: 0, finalScore: 0 },
        4: { votes: 0, points: 0, finalScore: 0 }
      }
    };
  });

  rounds.forEach(r => {
    const weightedImages = (db.images || [])
      .filter(i => i.ronda === r)
      .map(img => ({ ...img, weightedVotes: img.votes * r }));

    const imgs = weightedImages.slice().sort((a, b) => b.weightedVotes - a.weightedVotes);
    
    const participantsInRound = new Set(imgs.map(img => img.name));
    participantsInRound.forEach(name => {
      if (participants[name]) {
        const classificationPoints = 4;
        participants[name].puntos[r - 1] = classificationPoints;
        participants[name].roundData[r] = {
          votes: 0,
          points: classificationPoints,
          finalScore: (0 * r) + classificationPoints
        };
      }
    });

    imgs.slice(0, 3).forEach((img, idx) => {
      const rank = idx + 1;
      let classificationPoints = 0;
      
      if (rank === 1) classificationPoints = 10;
      else if (rank === 2) classificationPoints = 8;
      else if (rank === 3) classificationPoints = 6;
      
      const finalRoundScore = (img.votes * r) + classificationPoints;

      if (participants[img.name]) {
        participants[img.name].puntos[r - 1] = classificationPoints;
        participants[img.name].roundData[r] = {
          votes: img.votes,
          points: classificationPoints,
          finalScore: finalRoundScore
        };
      }
    });
  });

  // CAMBIO CLAVE: Recalcular el total final sumando los 'finalScore' de cada ronda
  Object.values(participants).forEach(p => {
    p.total = p.roundData[1].finalScore + p.roundData[2].finalScore + p.roundData[3].finalScore + p.roundData[4].finalScore;
  });

  return Object.values(participants).sort((a, b) => b.total - a.total);
}


/**
 * REGISTRO DE NUEVOS USUARIOS
 * POST /api/register
 * body: { name, password }
 * 
 * Migracion OK
 */
app.post('/api/register', async (req, res) => {
  const { name, password } = req.body;

  if (!name || !password) {
    return res.status(400).json({ message: 'Nombre y contraseña son requeridos.' });
  }

  const db = await readDB();

  // Verificar si el usuario ya existe
  const userExists = db.users.find(u => u.name.toLowerCase() === name.toLowerCase());
  if (userExists) {
    return res.status(400).json({ message: 'El nombre de usuario ya está en uso.' });
  }

  // Encriptar la contraseña antes de guardarla
  const saltRounds = 10;
  const hashedPassword = await bcrypt.hash(password, saltRounds);

  // Crear el nuevo usuario
  const newUser = {
    id: Date.now().toString(),
    name: name,
    password: hashedPassword // Guardamos la contraseña encriptada
  };

  db.users.push(newUser);
  await writeDB(db);

  res.status(201).json({ success: true, message: 'Usuario registrado con éxito.' });
});


/**
 * LOGIN DE USUARIOS
 * POST /api/login
 * body: { name, password }
 * 
 * Migracion OK
 */
app.post('/api/login', async (req, res) => {
  const { name, password } = req.body;

  if (!name || !password) {
    return res.status(400).json({ message: 'Nombre y contraseña son requeridos.' });
  }

  const db = await readDB();

  // 1. Buscar al usuario por su nombre
  const user = db.users.find(u => u.name.toLowerCase() === name.toLowerCase());
  if (!user) {
    return res.status(401).json({ message: 'Credenciales inválidas.' }); // Mensaje genérico por seguridad
  }

  // 2. Comparar la contraseña enviada con la encriptada en la DB
  const isPasswordCorrect = await bcrypt.compare(password, user.password);
  if (!isPasswordCorrect) {
    return res.status(401).json({ message: 'Credenciales inválidas.' });
  }

  // 3. Si todo es correcto, crear el token JWT
  const tokenPayload = {
    id: user.id,
    name: user.name
  };

  const token = jwt.sign(tokenPayload, JWT_SECRET, { expiresIn: '1d' }); // El token expira en 1 día

  // 4. Enviar el token al usuario
  res.json({ success: true, token: token });
});

app.get('/api/dashboard', async (req, res) => {
  const db = await readDB();
  const scores = computeScores(db);
  res.json({ images: db.images, votes: db.votes, scores });
});

/* util: listar todas las imágenes */
app.get('/api/images', async (req, res) => {
  const db = await readDB();
  res.json(db.images);
});


/**
 * GET /api/temas/:ronda
 * Obtiene los temas para una ronda específica y el estado de voto del usuario.
 * Protegido por autenticación.
 */
app.get('/api/temas/:ronda', authMiddleware, async (req, res) => {
  const { ronda } = req.params;
  const userId = req.user.id;

  const db = await readDB();

  const temas = db.themes.filter(t => t.ronda === Number(ronda));

  // Buscamos el voto específico del usuario en esta ronda
  const votoExistente = db.themeVotes.find(v => v.userId === userId && v.ronda === Number(ronda));

  res.json({
    temas: temas,
    // Si hay un voto, 'haVotado' será true
    haVotado: !!votoExistente, 
    // NUEVO: Enviamos el ID del tema por el que se votó, o null si no se ha votado
    userVotedFor: votoExistente ? votoExistente.themeId : null 
  });
});


/**
 * POST /api/temas/votar
 * Registra el voto de un usuario para un tema en una ronda.
 * Protegido por autenticación.
 * body: { themeId }
 */
app.post('/api/temas/votar', authMiddleware, async (req, res) => {
  const { themeId } = req.body;
  const userId = req.user.id;

  if (!themeId) {
    return res.status(400).json({ message: 'Se requiere el ID del tema.' });
  }

  const db = await readDB();

  // Encontrar el tema por el que se está votando
  const theme = db.themes.find(t => t.id === themeId);
  if (!theme) {
    return res.status(404).json({ message: 'El tema no fue encontrado.' });
  }

  // ¡VALIDACIÓN CLAVE! Verificar que el usuario no haya votado ya en esta ronda.
  const votoExistente = db.themeVotes.find(v => v.userId === userId && v.ronda === theme.ronda);
  if (votoExistente) {
    return res.status(400).json({ message: 'Ya has votado en esta ronda.' });
  }

  // Si todo es válido, registramos el voto
  // 1. Aumentamos el contador en el tema
  theme.votes++;

  // 2. Añadimos un registro del voto para evitar duplicados
  db.themeVotes.push({
    userId: userId,
    ronda: theme.ronda,
    themeId: themeId
  });

  await writeDB(db);

  res.json({ success: true, message: 'Voto registrado con éxito.' });
});


/**
 * GET /api/me/votes
 * Devuelve el historial de votos del usuario autenticado.
 * 
 * Migracion OK
 */
app.get('/api/me/votes', authMiddleware, async (req, res) => {
  const userId = req.user.id;
  const db = await readDB();

  // Filtra todos los votos para encontrar los de este usuario
  const userImageVotes = db.votes.filter(v => v.voterName === req.user.name);

  // Formatea los datos para que el frontend los entienda fácilmente: { ronda: imageId }
  const formattedVotes = {};
  userImageVotes.forEach(vote => {
    formattedVotes[vote.ronda] = vote.imageId;
  });

  res.json(formattedVotes);
});


/**
 * GET /api/stats
 * Devuelve estadísticas básicas: total de participantes, total votos emitidos en temas y total de votos emitidos.
 */
app.get('/api/stats', async (req, res) => {
  try {
    const db = await readDB();
    const totalParticipants = (db.users || []).length;
    const totalImageVotes = (db.votes || []).length; // Votos para imágenes
    const totalThemeVotes = (db.themeVotes || []).length; // Votos para temas

    res.json({
      totalParticipants: totalParticipants,
      totalImageVotes: totalImageVotes,
      totalThemeVotes: totalThemeVotes
    });
  } catch (err) {
    console.error("Error fetching stats:", err);
    res.status(500).json({ message: "Error al obtener estadísticas." });
  }
});


/**
 * GET /api/stats/temas/:ronda
 * Devuelve el número total de votos emitidos para temas en una ronda específica.
 */
app.get('/api/stats/temas/:ronda', async (req, res) => {
  const { ronda } = req.params;
  try {
    const db = await readDB();
    // Filtra los votos de temas por la ronda especificada y cuenta cuántos hay
    const roundThemeVotesCount = (db.themeVotes || []).filter(v => v.ronda === Number(ronda)).length;

    res.json({
      round: Number(ronda),
      totalVotesInRound: roundThemeVotesCount
    });
  } catch (err) {
    console.error("Error al obtener votos del tema para la ronda:", err);
    res.status(500).json({ message: "Error al obtener votos del tema para la ronda." });
  }
});


/**
 * GET /api/stats/images/:ronda
 * Devuelve el número total de votos emitidos para imágenes en una ronda específica.
 */
app.get('/api/stats/images/:ronda', async (req, res) => {
  const { ronda } = req.params;
  try {
    const db = await readDB();
    // Filtra los votos de imágenes por la ronda especificada y cuenta cuántos hay
    const roundImageVotesCount = (db.votes || []).filter(v => v.ronda === Number(ronda)).length;

    res.json({
      round: Number(ronda),
      totalVotesInRound: roundImageVotesCount
    });
  } catch (err) {
    console.error("Error al obtener votos de imágenes para la ronda:", err);
    res.status(500).json({ message: "Error al obtener votos de imágenes para la ronda." });
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Backend: http://localhost:${PORT} (accessible en la LAN por la IP de tu equipo)`);
});
