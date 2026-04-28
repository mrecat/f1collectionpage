<?php
define('DB_PATH', __DIR__ . '/data/collection.db');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        if (!is_dir(__DIR__ . '/data')) {
            mkdir(__DIR__ . '/data', 0755, true);
        }
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        initDB($pdo);
    }
    return $pdo;
}

function initDB(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cars (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            year       INTEGER NOT NULL,
            team       TEXT NOT NULL,
            model      TEXT NOT NULL,
            driver     TEXT NOT NULL,
            maker      TEXT NOT NULL,
            collection TEXT NOT NULL,
            note       TEXT,
            image_path TEXT DEFAULT NULL,
            favorite   INTEGER DEFAULT 0,
            created_at TEXT DEFAULT (datetime('now'))
        )
    ");
    // Add image_path if upgrading from v1
    try { $pdo->exec("ALTER TABLE cars ADD COLUMN image_path TEXT DEFAULT NULL"); } catch(Exception $e) {}
    // Add performance field if upgrading
    try { $pdo->exec("ALTER TABLE cars ADD COLUMN performance TEXT DEFAULT NULL"); } catch(Exception $e) {}
    // Add champion flag if upgrading
    try { $pdo->exec("ALTER TABLE cars ADD COLUMN is_champion INTEGER DEFAULT 0"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE cars ADD COLUMN is_team_champion INTEGER DEFAULT 0"); } catch(Exception $e) {}

    // Tabla de configuración general (about, etc.)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            key   TEXT PRIMARY KEY,
            value TEXT
        )
    ");

    // Tabla de múltiples imágenes por auto
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS car_images (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            car_id     INTEGER NOT NULL,
            path       TEXT NOT NULL,
            label      TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
        )
    ");

    // Migrar image_path legacy → car_images (se ejecuta una sola vez)
    $legacy = $pdo->query("SELECT id, image_path FROM cars WHERE image_path IS NOT NULL AND image_path != ''")->fetchAll();
    foreach ($legacy as $row) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM car_images WHERE car_id=? AND path=?");
        $chk->execute([$row['id'], $row['image_path']]);
        if ((int)$chk->fetchColumn() === 0) {
            $pdo->prepare("INSERT INTO car_images (car_id, path, sort_order) VALUES (?,?,0)")
                ->execute([$row['id'], $row['image_path']]);
        }
    }

    // Limpiar referencias a imágenes que ya no existen en disco
    cleanOrphanImages($pdo);

    // Seed data only if empty
    $count = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
    if ($count == 0) {
        seedData($pdo);
    }
}

function cleanOrphanImages(PDO $pdo): void {
    $imgs = $pdo->query("SELECT id, path FROM car_images")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($imgs as $img) {
        $full = __DIR__ . '/' . $img['path'];
        if (!file_exists($full)) {
            $pdo->prepare("DELETE FROM car_images WHERE id = ?")->execute([$img['id']]);
        }
    }
    // Limpiar también image_path legacy
    $legacy = $pdo->query("SELECT id, image_path FROM cars WHERE image_path IS NOT NULL AND image_path != ''")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($legacy as $row) {
        if (!file_exists(__DIR__ . '/' . $row['image_path'])) {
            $pdo->prepare("UPDATE cars SET image_path = NULL WHERE id = ?")->execute([$row['id']]);
        }
    }
}

function seedData(PDO $pdo): void {
    $data = [
        [1936,'Auto Union','TYP C','Bernd Rosemeyer','IXO','Leyendas de la F1 - España','Bestia de motor central V16; gloria de preguerra.'],
        [1950,'Alfa Romeo','Alfa Romeo 158','Nino Farina','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El primer auto campeón de la historia de la F1 (1950).'],
        [1951,'Alfa Romeo','Alfa Romeo 159','Juan Manuel Fangio','IXO / Coleccion Fangio','Museo Fangio','El auto del título mundial de Juan Manuel Fangio.'],
        [1951,'Ferrari','Ferrari 375 Indy','Alberto Ascari','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El Ferrari que compitió en las 500 Millas de Indianápolis.'],
        [1954,'Mercedes Benz','Mercedes W196 (sin carenar)','Juan Manuel Fangio','IXO / Coleccion Fangio','Museo Fangio','Las "Flechas de Plata" originales en versión clásica.'],
        [1955,'Mercedes Benz','Mercedes W196','Juan Manuel Fangio','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Versión carenada para circuitos de alta velocidad (Monza).'],
        [1955,'Lancia','Lancia D50','Alberto Ascari','Leyendas De La F1','Leyendas de la F1 - Planeta DeAgostini','El diseño que fusionó a Lancia con la Scuderia Ferrari.'],
        [1956,'Maserati','Maserati 250F','Juan Manuel Fangio','IXO / Coleccion Fangio','Museo Fangio','Victoria épica de Fangio en Nürburgring 1957.'],
        [1957,'Maserati','Maserati 250F','Juan Manuel Fangio','IXO / Coleccion Fangio','Museo Fangio','Victoria épica de Fangio en Nürburgring 1957.'],
        [1957,'Maserati','Maserati 250F','Juan Manuel Fangio','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto más equilibrado y exitoso de la década del 50.'],
        [1958,'Ferrari','Ferrari 246 F1','Mike Hawthorn','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El último campeón del mundo con motor delantero.'],
        [1963,'Lotus','Lotus 25','Jim Clark','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Introducción del chasis monocasco; liviano y revolucionario.'],
        [1964,'Ferrari','Ferrari 512 F1','Lorenzo Bandini','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El último V12 de Ferrari antes de la era de 3 litros.'],
        [1965,'Lancia / Ferrari','Lancia D50','Juan Manuel Fangio','IXO / Coleccion Fangio','Museo Fangio','Innovador diseño con tanques de nafta laterales.'],
        [1967,'Brabham','Brabham BT24','Denis Hulme','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Filosofía de simplicidad y ligereza de Jack Brabham.'],
        [1967,'Eagle','Eagle Mk1','Dan Gurney','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Uno de los F1 más estéticos de la historia; motor Gurney-Weslake.'],
        [1967,'Honda','Honda RA300','John Surtees','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La potencia del motor V12 japonés en el chasis Lola.'],
        [1968,'Lotus','Lotus 49B','Graham Hill','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Primer auto con alerones integrados y publicidad comercial.'],
        [1969,'Matra','Matra MS80','Jackie Stewart','IXO / Altaya España','Leyendas de la F1 - España','Campeón 1969. Chasis francés con motor Ford Cosworth.'],
        [1969,'Matra','Matra MS10','Jackie Stewart','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El título de Jackie Stewart con chasis francés.'],
        [1970,'BRM','BRM P153','Pedro Rodríguez','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El motor V12 de BRM en su máxima expresión.'],
        [1971,'Lotus','Lotus 56B','Emerson Fittipaldi','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El experimento fallido pero fascinante de motor a turbina.'],
        [1971,'March','March 711','Ronnie Peterson','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Famoso por su ala delantera tipo "bandeja de té".'],
        [1972,'BRM','BRM P160B','Jean-Pierre Beltoise','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Histórica victoria de Beltoise en Mónaco bajo lluvia.'],
        [1972,'Ferrari','Ferrari 312 B2','Mario Andretti','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El motor Flat-12 de Ferrari; sonido inconfundible.'],
        [1972,'Lotus','Lotus 72D','Emerson Fittipaldi','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El JPS negro y oro; perfección del diseño en cuña.'],
        [1973,'Tyrrell','Tyrrell 006','Jackie Stewart','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto del retiro del tricampeón Jackie Stewart.'],
        [1975,'Brabham','Brabham BT44B','Carlos Pace','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Diseño de Gordon Murray; única victoria de Carlos Pace.'],
        [1975,'Ferrari','Ferrari 312 B3','Clay Regazzoni','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La evolución hacia el efecto suelo de Maranello.'],
        [1975,'Hesketh','Hesketh 308B','James Hunt','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El equipo de Lord Hesketh; lujo y velocidad sin sponsors.'],
        [1975,'March','March 751','Vittorio Brambilla','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El "Gorila de Monza" ganando en Austria bajo el diluvio.'],
        [1976,'Brabham','Brabham BT45B','Carlos Reutemann','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El motor Alfa Romeo en el chasis blanco de Brabham.'],
        [1976,'McLaren','McLaren M23','James Hunt','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto del duelo Hunt-Lauda en la película "Rush".'],
        [1976,'Penske','Penske PC4','John Watson','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Única victoria de un auto 100% estadounidense en F1.'],
        [1976,'Tyrrell','Tyrrell P34','Jody Scheckter','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El icónico experimento de 6 ruedas; ganador en Suecia.'],
        [1977,'ATS','ATS PC4','Jean-Pierre Jarier','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El equipo alemán ATS utilizando chasis de Penske.'],
        [1977,'Ferrari','Ferrari 312 T2','Gilles Villeneuve','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El título de Niki Lauda tras su regreso milagroso.'],
        [1977,'Ferrari','Ferrari 312 T2','Niki Lauda','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El título de Niki Lauda tras su regreso milagroso.'],
        [1977,'McLaren','McLaren M23','Gilles Villeneuve','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El debut de Gilles Villeneuve en F1 (GP de Inglaterra).'],
        [1977,'Renault','Renault RS01','Jean-Pierre Jabouille','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La llegada del motor Turbo; "La tetera amarilla".'],
        [1977,'Wolf','Wolf WR1','Jody Scheckter','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Tres victorias en el año de debut como constructor.'],
        [1978,'Brabham','Brabham BT46B','Niki Lauda','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El "Fan Car" (auto ventilador); prohibido por su ventaja.'],
        [1978,'Lotus','Lotus 79','Mario Andretti','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El "Black Beauty"; perfeccionó el efecto suelo moderno.'],
        [1979,'Alfa Romeo','Alfa Romeo 177','Bruno Giacomelli','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El regreso de Alfa Romeo como constructor oficial.'],
        [1979,'Arrows','Arrows A1','Riccardo Patrese','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto de la disputa legal por el diseño con Shadow.'],
        [1979,'Ferrari','Ferrari 312 T3','Jody Scheckter','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El campeonato mundial de Jody Scheckter.'],
        [1979,'Ligier','Ligier JS11','Jacques Laffite','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Dominio francés con el efecto suelo y motores Ford.'],
        [1979,'Renault','Renault RS10','Jean-Pierre Jabouille','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Primera victoria de un motor Turbo en un Gran Premio.'],
        [1979,'Shadow','Shadow DN9','Elio De Angelis','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El debut del talentoso Elio De Angelis.'],
        [1979,'Williams','Williams FW07','Clay Regazzoni','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La primera victoria de la historia para Williams.'],
        [1980,'Williams','Williams FW07B','Alan Jones','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto del primer título de Alan Jones con Williams.'],
        [1980,'Williams','Williams FW07B','Carlos Reutemann','IXO / Salvat','Racing Cars - Salvat','El Lole Reutemann peleando el título mundial 1980/81.'],
        [1981,'Brabham','Brabham BT49','Nelson Piquet','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El primer título mundial de Nelson Piquet.'],
        [1982,'Ferrari','Ferrari 126 C2','Mario Andretti','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La era turbo de Ferrari; potencia extrema y fragilidad.'],
        [1982,'Tyrrell','Tyrrell 011','Michele Alboreto','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La última victoria de un motor aspirado ante los Turbo.'],
        [1982,'Williams','Williams FW08','Keke Rosberg','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Keke Rosberg campeón con una sola victoria en el año.'],
        [1983,'Brabham','Brabham BT52B','Nelson Piquet','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El diseño de "flecha" de Murray; campeón con BMW Turbo.'],
        [1984,'Toleman','Toleman TG184','Ayrton Senna','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El debut de Ayrton Senna; podio épico en Mónaco.'],
        [1985,'Lotus','Lotus 97T','Ayrton Senna','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Primera victoria de Senna (Estoril) con el JPS negro.'],
        [1985,'McLaren','McLaren MP4/2B','Alain Prost','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El primer título de Alain Prost; motor TAG-Porsche.'],
        [1986,'Benetton','Benetton B186','Gerhard Berger','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La potencia bruta del motor BMW (1300HP en clasificación).'],
        [1987,'Lotus','Lotus 99T','Ayrton Senna','IXO / Salvat','Racing Cars - Salvat','Suspensión activa y motor Honda (Amarillo Camel).'],
        [1987,'Williams','Williams FW11B','Nelson Piquet','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El título de Nelson Piquet contra su compañero Mansell.'],
        [1988,'McLaren','McLaren MP4/4','Ayrton Senna','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto más exitoso: 15 victorias en 16 carreras.'],
        [1990,'Benetton','Benetton B190','Nelson Piquet','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Las últimas victorias de Piquet (GP de Japón/Australia).'],
        [1991,'Jordan','Jordan 191','Michael Schumacher','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El debut de Michael Schumacher en Spa-Francorchamps.'],
        [1992,'Williams','Williams FW14B','Nigel Mansell','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El "Red 5" de Mansell; superioridad tecnológica total.'],
        [1993,'McLaren','McLaren MP4/8','Ayrton Senna','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Magia de Senna bajo la lluvia (Donington Park \'93).'],
        [1993,'Williams','Williams FW15C','Alain Prost','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El cuarto y último título mundial de Alain Prost.'],
        [1994,'Benetton','Benetton B194','Michael Schumacher','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Primer título del Kaiser Schumacher; motor Ford V8.'],
        [1996,'Benetton','Benetton B196','Jean Alesi','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El equipo de los colores unidos post-Schumacher.'],
        [1996,'Ligier','Ligier JS43','Olivier Panis','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Sorpresiva victoria en Mónaco con solo 3 autos en meta.'],
        [1997,'Williams','Williams FW19','Jacques Villeneuve','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El hijo de Gilles Villeneuve ganando el mundial.'],
        [1999,'Ferrari','Ferrari F399','Mika Salo','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Michael Salo reemplazando al lesionado Schumacher.'],
        [1999,'Jordan','Jordan 199','Heinz-Harald Frentzen','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El mejor año de Jordan; 3ros en el mundial de equipos.'],
        [1999,'McLaren','McLaren MP4/14','Mika Häkkinen','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El bicampeonato del finlandés Mika Häkkinen.'],
        [1999,'Stewart','Stewart SF03','Johnny Herbert','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Única victoria de Sir Jackie Stewart como dueño de equipo.'],
        [2000,'BAR','BAR 002','Jacques Villeneuve','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La ambiciosa apuesta de British American Tobacco.'],
        [2001,'Ferrari','Ferrari F2001','Michael Schumacher','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Dominio absoluto de Ferrari en el nuevo milenio.'],
        [2002,'Ferrari','Ferrari F2002','Michael Schumacher','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto perfecto; casi invencible en manos de Schumi.'],
        [2003,'Jaguar','Jaguar R4','Mark Webber','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El elegante "British Racing Green" de Ford/Jaguar.'],
        [2004,'Ferrari','Ferrari F2004','Rubens Barrichello','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Récords de vuelta que duraron más de una década.'],
        [2004,'Renault','Renault R24','Jarno Trulli','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El azul de Fernando Alonso y su primer podio.'],
        [2004,'Renault','Renault R24','Fernando Alonso','IXO / Salvat','Racing Cars - Salvat','El azul de Fernando Alonso y su primer podio.'],
        [2004,'Sauber','Sauber C23','Felipe Massa','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Motor Ferrari en chasis suizo; el debut de Massa.'],
        [2004,'Williams','Williams FW26','Juan Pablo Montoya','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El innovador (y extraño) diseño de "morro de morsa".'],
        [2005,'Renault','Renault R25','Fernando Alonso','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto que terminó con el reinado de Michael Schumacher.'],
        [2006,'Honda','Honda RA106','Jenson Button','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Primera victoria de Jenson Button (Hungría).'],
        [2007,'Ferrari','Ferrari F2007','Kimi Räikkönen','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El último título de pilotos de Ferrari hasta hoy.'],
        [2008,'BMW Sauber','BMW Sauber F1.08','Robert Kubica','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Única victoria de BMW como constructor (Kubica).'],
        [2008,'McLaren','McLaren MP4/23','Lewis Hamilton','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El título de Lewis Hamilton en la última curva de Brasil.'],
        [2008,'Toro Rosso','Toro Rosso STR3','Sebastian Vettel','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El milagro de Monza; primera victoria de Vettel.'],
        [2009,'Brawn','Brawn GP 001','Jenson Button','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El equipo que nació de las cenizas y ganó todo.'],
        [2010,'Ferrari','Ferrari F10','Felipe Massa','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El regreso de Fernando Alonso a Ferrari.'],
        [2010,'HRT','HRT F110','Bruno Senna','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El humilde equipo español con el sobrino de Senna.'],
        [2012,'Red Bull','Red Bull RB11','Daniel Ricchardo','BBurago','BBurago F1','El auto con el diseño "camuflaje" en pretemporada.'],
        [2012,'Lotus','Lotus E20','Kimi Räikkönen','IXO / Salvat','Fórmula 1 Car Collection - Salvat','"I know what I\'m doing": victoria de Kimi en Abu Dhabi.'],
        [2012,'Williams','Williams FW34','Pastor Maldonado','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Histórica victoria de Pastor Maldonado en España.'],
        [2013,'Ferrari','Ferrari F138','Fernando Alonso','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El último auto de Alonso ganando para Ferrari.'],
        [2013,'Red Bull','Red Bull RB9','Sebastian Vettel','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El tetracampeonato de Vettel; 9 victorias seguidas.'],
        [2014,'Mercedes Benz','Mercedes F1 W05','Lewis Hamilton','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El inicio de la era Híbrida y el dominio de Mercedes.'],
        [2014,'Williams','Williams FW36','Valtteri Bottas','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El renacimiento de Williams con motor Mercedes.'],
        [2015,'Ferrari','Ferrari SF15-T','Sebastian Vettel','BBurago','BBurago F1','Primera victoria de Vettel con Ferrari (GP Malasia).'],
        [2015,'Ferrari','Ferrari SF15-T','Sebastian Vettel','IXO / Salvat','Fórmula 1 Car Collection - Salvat','Primera victoria de Vettel vestido de rojo Ferrari.'],
        [2016,'Force India','Force India VJM09','Sergio Pérez','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El "Checo" Pérez logrando podios con Force India.'],
        [2016,'Mercedes Benz','Mercedes W07','Nico Rosberg','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El año en que Nico Rosberg batió a Hamilton.'],
        [2016,'Red Bull','Red Bull RB12','Max Verstappen','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El ganador más joven de la historia (Verstappen).'],
        [2017,'Ferrari','Ferrari SF70H','Sebastian Vettel','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto que devolvió la esperanza a los Tifosi.'],
        [2017,'Force India','Force India VJM10','Sergio Pérez','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El icónico diseño rosa (BWT) de Sergio Pérez.'],
        [2017,'Mercedes Benz','Mercedes W08','Lewis Hamilton','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto más rápido de la historia en clasificación.'],
        [2017,'Toro Rosso','Toro Rosso STR12','Carlos Sainz Jr.','IXO / Salvat','Fórmula 1 Car Collection - Salvat','El auto del ascenso de Carlos Sainz hacia Ferrari.'],
        [2018,'Ferrari','Ferrari SF71H','Sebastian Vettel','IXO / Salvat','Fórmula 1 Car Collection - Salvat','La lucha técnica cabeza a cabeza contra Mercedes.'],
        [2020,'Ferrari','Ferrari SF1000','Sebastian Vettel','BBurago','BBurago F1','Edición 1000 GPs de Ferrari; color rojo borravino clásico.'],
        [2020,'Mercedes Benz','Mercedes W11 EQ Performance','Lewis Hamilton','IXO / Salvat','Racing Cars - Salvat','El récord histórico de títulos de Lewis Hamilton.'],
        [2021,'Mercedes Benz','Mercedes AMG W12E','Lewis Hamilton','BBurago','BBurago F1','El auto del polémico final de Abu Dhabi 2021.'],
        [2021,'Aston Martin','Aston Martin AMR21','Sebastian Vettel','IXO / Salvat','Racing Cars - Salvat','El regreso del verde británico a la parrilla.'],
        [2021,'Ferrari','Ferrari SF21','Carlos Sainz Jr.','IXO / Salvat','Racing Cars - Salvat','El auto de la ajustada pelea contra McLaren en 2021.'],
        [2021,'McLaren','McLaren MCL35M','Lando Norris','IXO / Salvat','Racing Cars - Salvat','El McLaren que volvió al podio con motor Mercedes.'],
        [2022,'Ferrari','Ferrari F1-75','Carlos Sainz Jr.','BBurago','BBurago F1','El regreso del efecto suelo a Ferrari; ganador en Bahrein.'],
        [2022,'Ferrari','Ferrari F1-75','Charles Leclerc','BBurago','BBurago F1','El regreso del efecto suelo a Ferrari; ganador en Bahrein.'],
        [2022,'McLaren','McLaren MCL36','Lando Norris','BBurago','BBurago F1','El McLaren que volvió a ser competitivo en la zona media.'],
        [2022,'McLaren','McLaren MCL36','Daniel Ricchardo','BBurago','BBurago F1','El McLaren que volvió a ser competitivo en la zona media.'],
        [2022,'Mercedes Benz','Mercedes AMG W13E Performance','George Russel','BBurago','BBurago F1','El "Zero-pod" Mercedes; diseño radical sin pontones.'],
        [2022,'Alpine','Alpine A522','Esteban Ocon','IXO / Salvat','Racing Cars - Salvat','Inicio de la nueva reglamentación aerodinámica.'],
        [2022,'Ferrari','Ferrari F1-75','Charles Leclerc','IXO / Salvat','Racing Cars - Salvat','El auto que cortó la racha de sequía de Ferrari.'],
        [2022,'Red Bull','Red Bull RB18','Max Verstappen','IXO / Salvat','Racing Cars - Salvat','El inicio de la era de dominación de Max Verstappen.'],
        [2023,'Alfa Romeo','Alfa Romeo C43','Valtteri Bottas','BBurago','BBurago F1','El adiós de Alfa Romeo de la F1 moderna.'],
        [2023,'Alfa Romeo','Alfa Romeo C43','Zhou Guanyu','BBurago','BBurago F1','El adiós de Alfa Romeo de la F1 moderna.'],
        [2023,'Alpine','Alpine A523','Esteban Ocon','BBurago','BBurago F1','Primer auto de la nueva era Alpine (post-Renault).'],
        [2023,'Aston Martin','Aston Martin AMR23','Lance Stroll','BBurago','BBurago F1','El "cohete verde" que revivió la carrera de Alonso.'],
        [2023,'Ferrari','Ferrari SF23','Carlos Sainz Jr.','BBurago','BBurago F1','Ganador en Singapur; único auto no-Red Bull en ganar en 2023.'],
        [2023,'Ferrari','Ferrari SF23','Charles Leclerc','BBurago','BBurago F1','Ganador en Singapur; único auto no-Red Bull en ganar en 2023.'],
        [2023,'Ferrari','Ferrari SF23 (Las Vegas GP)','Charles Leclerc','BBurago','BBurago F1','Decoración especial blanca y roja para el GP de Las Vegas.'],
        [2023,'McLaren','McLaren MCL 60','Oscar Piastri','BBurago','BBurago F1','El auto del "Papaya Rule" y el ascenso de Piastri.'],
        [2023,'McLaren','McLaren MCL60 (Monaco GP)','Oscar Piastri','BBurago','BBurago F1','Decoración especial "Triple Corona" para Mónaco.'],
        [2023,'Mercedes Benz','Mercedes AMG W14E Performance','Lewis Hamilton','BBurago','BBurago F1','El regreso al color negro para ahorrar peso de pintura.'],
        [2023,'Red Bull','Red Bull RB19','Sergio Perez','BBurago','BBurago F1','El auto más dominante de la historia (21 victorias en 22 GPs).'],
        [2023,'Red Bull','Red Bull RB19','Max Verstappen','BBurago','BBurago F1','El auto más dominante de la historia (21 victorias en 22 GPs).'],
        [2023,'Aston Martin','Aston Martin AMR23','Fernando Alonso','IXO / Salvat','Racing Cars - Salvat','El espectacular renacimiento de Fernando Alonso.'],
        [2023,'Mercedes Benz','Mercedes AMG W14E Performance (Australian GP)','Lewis Hamilton','IXO / Salvat','Racing Cars - Salvat','Decoración especial para el GP de Australia.'],
        [2023,'Red Bull','Red Bull RB19','Sergio Perez','IXO / Salvat','Racing Cars - Salvat','El auto que rompió el récord de victorias en un año.'],
        [2024,'Alpine','Alpine A524','Pierre Gasly','BBurago','BBurago F1','Evolución aerodinámica con enfoque en el flujo de aire.'],
        [2024,'Alpine','Alpine A524','Estaban Ocon','BBurago','BBurago F1','Evolución aerodinámica con enfoque en el flujo de aire.'],
        [2024,'Ferrari','Ferrari SF24','Carlos Sainz Jr.','BBurago','BBurago F1','El auto con el que Carlos Sainz ganó en Australia 2024.'],
        [2024,'Ferrari','Ferrari SF24 (Miami GP)','Carlos Sainz Jr.','BBurago','BBurago F1','Edición especial con detalles en azul (Azzurro La Plata).'],
        [2024,'Ferrari','Ferrari SF24 (Miami GP)','Charles Leclerc','BBurago','BBurago F1','Edición especial con detalles en azul (Azzurro La Plata).'],
        [2024,'McLaren','McLaren MCL 36 (Miami GP)','Lando Norris','BBurago','BBurago F1','Decoración especial para el primer GP de Miami.'],
        [2024,'McLaren','McLaren MCL38 (Monaco GP)','Oscar Piastri','BBurago','BBurago F1','Decoración "Triple Corona" / Senna para el GP de Mónaco.'],
        [2024,'McLaren','McLaren MCL38 (Monaco GP)','Lando Norris','BBurago','BBurago F1','Decoración "Triple Corona" / Senna para el GP de Mónaco.'],
        [2024,'Mercedes Benz','Mercedes AMG W15E Performance','George Russel','BBurago','BBurago F1','Evolución del concepto para volver a pelear podios.'],
        [2024,'Red Bull','Red Bull RB20 (Silverstone GP)','Max Verstappen','BBurago','BBurago F1','Decoración especial de los fans para el GP de Gran Bretaña.'],
        [2025,'Alpine','Alpine A525','Franco Colapinto','BBurago','BBurago F1','El regreso de Argentina a la F1: Franco Colapinto.'],
        [2025,'APX GP','APX (F1 movie)','Sonny Hayes (Brad Pit)','BBurago','McDonald\'s F1 Movie','El auto de ficción de la película "F1" de Brad Pitt.'],
        [2025,'APX GP','McDonald\'s (F1 movie)','','BBurago','McDonald\'s F1 Movie','Edición especial promocional de la película de cine.'],
        [2025,'Ferrari','Ferrari SF25','Lewis Hamilton','BBurago','BBurago F1','El debut histórico de Lewis Hamilton en Ferrari.'],
        [2025,'Ferrari','Ferrari SF25 (Miami GP)','Lewis Hamilton','BBurago','BBurago F1','Versión de Hamilton con decoración especial para Miami.'],
        [2025,'McLaren','McLaren MCL39 (Australian GP)','Lando Norris','BBurago','BBurago F1','El auto que llevó a McLaren a liderar el campeonato 2025.'],
        [2025,'Mercedes Benz','Mercedes AMG W16','Kimi Antonelli','BBurago','BBurago F1','El debut de Kimi Antonelli reemplazando a Hamilton.'],
        [2025,'Red Bull','Red Bull RB21','Max Verstappen','BBurago','BBurago F1','El primer RB con la nueva reglamentación de unidades de potencia.'],
        [2025,'Red Bull','Red Bull RB21 (Japan GP)','Max Verstappen','BBurago','BBurago F1','Configuración de alta carga para el circuito de Suzuka.'],
    ];

    $stmt = $pdo->prepare("INSERT INTO cars (year,team,model,driver,maker,collection,note) VALUES (?,?,?,?,?,?,?)");
    foreach ($data as $row) {
        $stmt->execute($row);
    }
}

// ─── Query helpers ───────────────────────────────────────────────────────────

function getCars(array $filters = [], string $sortField = 'year', string $sortDir = 'asc'): array {
    $db  = getDB();
    $sql = "SELECT * FROM cars WHERE 1=1";
    $params = [];

    if (!empty($filters['q'])) {
        $q = '%' . $filters['q'] . '%';
        $sql .= " AND (driver LIKE ? OR team LIKE ? OR model LIKE ? OR note LIKE ?)";
        array_push($params, $q, $q, $q, $q);
    }
    if (!empty($filters['year'])) {
        $sql .= " AND year = ?";
        $params[] = $filters['year'];
    }
    if (!empty($filters['team'])) {
        $sql .= " AND team = ?";
        $params[] = $filters['team'];
    }
    if (!empty($filters['driver'])) {
        $sql .= " AND driver = ?";
        $params[] = $filters['driver'];
    }
    if (!empty($filters['maker'])) {
        $sql .= " AND maker = ?";
        $params[] = $filters['maker'];
    }
    if (!empty($filters['favorites'])) {
        $sql .= " AND favorite = 1";
    }

    // Whitelist para evitar injection
    $sf  = in_array($sortField, ['year','team']) ? $sortField : 'year';
    $sd  = $sortDir === 'desc' ? 'DESC' : 'ASC';
    // Siempre secondary sort por el otro campo
    $sec = ($sf === 'year') ? 'team ASC' : 'year ASC';
    $sql .= " ORDER BY $sf $sd, $sec";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getCarById(int $id): ?array {
    $stmt = getDB()->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Genera el slug de un auto: "ferrari-f2004-schumacher-2004"
function makeCarSlug(array $car): string {
    $raw = $car['year'] . '-' . $car['team'] . '-' . $car['model'] . '-' . $car['driver'] . '-' . $car['id'];
    $slug = mb_strtolower($raw, 'UTF-8');
    // Reemplazar caracteres con tilde
    $from = ['á','é','í','ó','ú','ü','ñ','Á','É','Í','Ó','Ú','Ü','Ñ'];
    $to   = ['a','e','i','o','u','u','n','a','e','i','o','u','u','n'];
    $slug = str_replace($from, $to, $slug);
    // Solo alfanuméricos y guiones
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

// Busca un auto por slug — extrae el ID del final del slug para lookup directo,
// con fallback a comparación completa para compatibilidad con slugs viejos sin ID.
function getCarBySlug(string $slug): ?array {
    $db = getDB();
    // Extraer ID del final del slug (formato: ...-{id})
    if (preg_match('/-(\d+)$/', $slug, $m)) {
        $id  = (int)$m[1];
        $car = $db->query("SELECT * FROM cars WHERE id = $id")->fetch();
        if ($car && makeCarSlug($car) === $slug) {
            return $car;
        }
    }
    // Fallback: comparación completa (compatibilidad con slugs viejos sin ID)
    $cars = $db->query("SELECT * FROM cars ORDER BY id ASC")->fetchAll();
    foreach ($cars as $car) {
        if (makeCarSlug($car) === $slug) {
            return $car;
        }
    }
    return null;
}

// Devuelve el auto anterior y siguiente (por año, luego id)
function getAdjacentCars(int $id): array {
    $db   = getDB();
    $all  = $db->query("SELECT id, year, team, model, driver FROM cars ORDER BY year ASC, id ASC")->fetchAll();
    $ids  = array_column($all, null, 'id');
    $keys = array_keys($ids);
    $pos  = array_search($id, $keys);
    return [
        'prev' => ($pos > 0)                ? $all[$pos - 1] : null,
        'next' => ($pos < count($all) - 1)  ? $all[$pos + 1] : null,
    ];
}

function getTotalCars(): int {
    return (int) getDB()->query("SELECT COUNT(*) FROM cars")->fetchColumn();
}

function getDistinct(string $col): array {
    $col = preg_replace('/[^a-z_]/', '', $col);
    return getDB()->query("SELECT DISTINCT $col FROM cars WHERE $col != '' ORDER BY $col")->fetchAll(PDO::FETCH_COLUMN);
}

function getStats(): array {
    $db = getDB();
    return [
        'total'       => (int)$db->query("SELECT COUNT(*) FROM cars")->fetchColumn(),
        'favorites'   => (int)$db->query("SELECT COUNT(*) FROM cars WHERE favorite=1")->fetchColumn(),
        'teams'       => (int)$db->query("SELECT COUNT(DISTINCT team) FROM cars")->fetchColumn(),
        'drivers'     => (int)$db->query("SELECT COUNT(DISTINCT driver) FROM cars WHERE driver != ''")->fetchColumn(),
        'years'       => (int)$db->query("SELECT COUNT(DISTINCT year) FROM cars")->fetchColumn(),
        'by_maker'    => $db->query("SELECT maker, COUNT(*) as cnt FROM cars GROUP BY maker ORDER BY cnt DESC")->fetchAll(),
        'by_team'     => $db->query("SELECT team, COUNT(*) as cnt FROM cars GROUP BY team ORDER BY cnt DESC LIMIT 10")->fetchAll(),
        'by_driver'   => $db->query("SELECT driver, COUNT(*) as cnt FROM cars WHERE driver != '' GROUP BY driver ORDER BY cnt DESC LIMIT 10")->fetchAll(),
        'by_decade'   => $db->query("SELECT (year/10)*10 as decade, COUNT(*) as cnt FROM cars GROUP BY decade ORDER BY decade")->fetchAll(),
        'by_collection'=> $db->query("SELECT collection, COUNT(*) as cnt FROM cars GROUP BY collection ORDER BY cnt DESC")->fetchAll(),
        'by_year'      => $db->query("SELECT year, COUNT(*) as cnt FROM cars GROUP BY year ORDER BY year")->fetchAll(),
    ];
}

// ─── Mutations ───────────────────────────────────────────────────────────────

function toggleFavorite(int $id): void {
    getDB()->prepare("UPDATE cars SET favorite = 1-favorite WHERE id=?")->execute([$id]);
}

function deleteCar(int $id): void {
    getDB()->prepare("DELETE FROM cars WHERE id=?")->execute([$id]);
}

function saveCar(array $data, ?int $id = null): void {
    $db = getDB();
    if ($id) {
        $sql = "UPDATE cars SET year=?,team=?,model=?,driver=?,maker=?,collection=?,note=?";
        $params = [$data['year'],$data['team'],$data['model'],$data['driver'],
                   $data['maker'],$data['collection'],$data['note']];
        if (array_key_exists('image_path', $data)) {
            $sql .= ", image_path=?";
            $params[] = $data['image_path'];
        }
        if (array_key_exists('performance', $data)) {
            $sql .= ", performance=?";
            $params[] = $data['performance'];
        }
        if (array_key_exists('is_champion', $data)) {
            $sql .= ", is_champion=?";
            $params[] = (int)$data['is_champion'];
        }
        if (array_key_exists('is_team_champion', $data)) {
            $sql .= ", is_team_champion=?";
            $params[] = (int)$data['is_team_champion'];
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        $db->prepare($sql)->execute($params);
    } else {
        $db->prepare("INSERT INTO cars (year,team,model,driver,maker,collection,note) VALUES (?,?,?,?,?,?,?)")
           ->execute([$data['year'],$data['team'],$data['model'],$data['driver'],
                      $data['maker'],$data['collection'],$data['note']]);
    }
}

function handleImageUpload(int $carId): ?string {
    if (empty($_FILES['car_image']['tmp_name'])) return null;
    $file = $_FILES['car_image'];
    $allowed = ['image/jpeg','image/jpg','image/png','image/webp','image/gif'];
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;

    $dir = __DIR__ . '/data/images/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = 'car_' . $carId . '_' . time() . '.' . strtolower($ext);
    $dest = $dir . $name;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'data/images/' . $name;
    }
    return null;
}

function deleteCarImage(int $id): void {
    $car = getCarById($id);
    if ($car && $car['image_path']) {
        $full = __DIR__ . '/' . $car['image_path'];
        if (file_exists($full)) unlink($full);
        getDB()->prepare("UPDATE cars SET image_path=NULL WHERE id=?")->execute([$id]);
    }
}

// ─── Multi-imagen ─────────────────────────────────────────────────────────────

function getCarImages(int $carId): array {
    $stmt = getDB()->prepare("SELECT * FROM car_images WHERE car_id=? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$carId]);
    return $stmt->fetchAll();
}

function getFirstImage(int $carId): ?string {
    $stmt = getDB()->prepare("SELECT path FROM car_images WHERE car_id=? ORDER BY sort_order ASC, id ASC LIMIT 1");
    $stmt->execute([$carId]);
    $row = $stmt->fetch();
    return $row ? $row['path'] : null;
}

function handleMultiImageUpload(int $carId): void {
    if (empty($_FILES['car_images']['tmp_name'][0])) return;
    $files   = $_FILES['car_images'];
    $allowed = ['image/jpeg','image/jpg','image/png','image/webp','image/gif'];
    $dir     = __DIR__ . '/data/images/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    foreach ($files['tmp_name'] as $i => $tmp) {
        if (!$tmp || $files['error'][$i] !== UPLOAD_ERR_OK) continue;
        if (!in_array($files['type'][$i], $allowed))        continue;
        if ($files['size'][$i] > 5 * 1024 * 1024)          continue;
        $cnt = (int)getDB()->query("SELECT COUNT(*) FROM car_images WHERE car_id=$carId")->fetchColumn();
        if ($cnt >= 5) break;
        $ext  = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        $name = 'car_' . $carId . '_' . time() . '_' . $i . '.' . $ext;
        if (move_uploaded_file($tmp, $dir . $name)) {
            getDB()->prepare("INSERT INTO car_images (car_id, path, sort_order) VALUES (?,?,?)")
                ->execute([$carId, 'data/images/' . $name, $cnt]);
        }
    }
}

function deleteCarImageByIndex(int $carId, int $imageId): void {
    $stmt = getDB()->prepare("SELECT path FROM car_images WHERE id=? AND car_id=?");
    $stmt->execute([$imageId, $carId]);
    $row = $stmt->fetch();
    if ($row) {
        $full = __DIR__ . '/' . $row['path'];
        if (file_exists($full)) unlink($full);
        getDB()->prepare("DELETE FROM car_images WHERE id=? AND car_id=?")->execute([$imageId, $carId]);
    }
}

function deleteAllCarImages(int $carId): void {
    $stmt = getDB()->prepare("SELECT path FROM car_images WHERE car_id=?");
    $stmt->execute([$carId]);
    foreach ($stmt->fetchAll() as $row) {
        $full = __DIR__ . '/' . $row['path'];
        if (file_exists($full)) unlink($full);
    }
    getDB()->prepare("DELETE FROM car_images WHERE car_id=?")->execute([$carId]);
}

// ─── Timeline ─────────────────────────────────────────────────────────────────

function getTimelineData(): array {
    $stmt = getDB()->query("
        SELECT c.year, c.team, c.model, c.driver, c.id,
               (SELECT path FROM car_images ci WHERE ci.car_id = c.id
                ORDER BY ci.sort_order ASC, ci.id ASC LIMIT 1) as thumb
        FROM cars c
        ORDER BY c.year ASC, c.team ASC
    ");
    $rows   = $stmt->fetchAll();
    $byYear = [];
    foreach ($rows as $row) {
        $byYear[$row['year']][] = $row;
    }
    return $byYear;
}

function setCoverImage(int $carId, int $imageId): void {
    $db = getDB();
    // Poner todas en sort_order = 1, luego la elegida en 0
    $db->prepare("UPDATE car_images SET sort_order = 1 WHERE car_id = ?")->execute([$carId]);
    $db->prepare("UPDATE car_images SET sort_order = 0 WHERE id = ? AND car_id = ?")->execute([$imageId, $carId]);
}

// ─── Home page data ───────────────────────────────────────────────────────────

function getHomeData(): array {
    $db = getDB();

    // Stats
    $stats = [
        'total'  => (int)$db->query("SELECT COUNT(*) FROM cars")->fetchColumn(),
        'teams'  => (int)$db->query("SELECT COUNT(DISTINCT team) FROM cars")->fetchColumn(),
        'photos' => (int)$db->query("SELECT COUNT(*) FROM car_images")->fetchColumn(),
        'years'  => $db->query("SELECT MIN(year) as mn, MAX(year) as mx FROM cars")->fetch(),
    ];

    // Hero: auto aleatorio que tenga imagen
    $hero = $db->query("
        SELECT c.id, c.year, c.team, c.model, c.driver, c.note,
               COUNT(ci.id) as img_count,
               (SELECT path FROM car_images ci2 WHERE ci2.car_id = c.id
                ORDER BY ci2.sort_order ASC, ci2.id ASC LIMIT 1) as thumb
        FROM cars c
        JOIN car_images ci ON ci.car_id = c.id
        GROUP BY c.id
        ORDER BY RANDOM()
        LIMIT 1
    ")->fetch();

    // Mosaico: 12 autos con foto variados por era — incluye driver para hover
    $mosaic = $db->query("
        SELECT c.id, c.year, c.team, c.model, c.driver,
               (SELECT path FROM car_images ci WHERE ci.car_id = c.id
                ORDER BY ci.sort_order ASC, ci.id ASC LIMIT 1) as thumb
        FROM cars c
        WHERE EXISTS (SELECT 1 FROM car_images ci WHERE ci.car_id = c.id)
        ORDER BY RANDOM()
        LIMIT 12
    ")->fetchAll();

    // Últimos 3 autos agregados (mayor id con imagen)
    $recents = $db->query("
        SELECT c.id, c.year, c.team, c.model, c.driver, c.maker, c.note, c.created_at,
               (SELECT path FROM car_images ci2 WHERE ci2.car_id = c.id
                ORDER BY ci2.sort_order ASC, ci2.id ASC LIMIT 1) as thumb
        FROM cars c
        WHERE EXISTS (SELECT 1 FROM car_images ci WHERE ci.car_id = c.id)
        ORDER BY c.id DESC
        LIMIT 3
    ")->fetchAll();

    $latest = $recents[0] ?? null;

    return compact('stats', 'hero', 'mosaic', 'latest', 'recents');
}

// ─── Miniaturas (segunda foto = modelo a escala) ──────────────────────────────

function getMiniaturas(array $filters = []): array {
    $db  = getDB();
    $sql = "
        SELECT c.id, c.year, c.team, c.model, c.driver, c.maker, c.collection,
               ci.path as scale_img,
               (SELECT path FROM car_images ci2
                WHERE ci2.car_id = c.id
                ORDER BY ci2.sort_order ASC, ci2.id ASC LIMIT 1) as real_img
        FROM cars c
        JOIN car_images ci ON ci.car_id = c.id
        WHERE ci.sort_order = 1
    ";
    $params = [];

    if (!empty($filters['year'])) {
        $sql .= " AND c.year = ?";
        $params[] = $filters['year'];
    }
    if (!empty($filters['team'])) {
        $sql .= " AND c.team = ?";
        $params[] = $filters['team'];
    }

    $sf  = ($filters['sort'] ?? 'year') === 'team' ? 'team' : 'year';
    $sd  = ($filters['dir']  ?? 'asc')  === 'desc' ? 'DESC' : 'ASC';
    $sec = $sf === 'year' ? 'team ASC' : 'year ASC';
    $sql .= " ORDER BY c.$sf $sd, $sec";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ── Settings (about, etc.) ────────────────────────
function getSetting(string $key, string $default = ''): string {
    try {
        $row = getDB()->prepare("SELECT value FROM settings WHERE key = ?");
        $row->execute([$key]);
        $r = $row->fetch();
        return $r ? $r['value'] : $default;
    } catch (Exception $e) { return $default; }
}

function saveSetting(string $key, string $value): void {
    getDB()->prepare("INSERT INTO settings (key, value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value")
           ->execute([$key, $value]);
}
