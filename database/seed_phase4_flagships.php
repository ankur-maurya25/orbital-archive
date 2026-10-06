<?php
/**
 * ORBITAL ARCHIVE - Phase 4 Flagship Data Seeder
 * Populates authentic research-backed data across 10 flagship spacecraft,
 * rovers, and landers from NASA, ESA, ISRO, and JAXA official records.
 */

require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

echo "Starting Phase 4 Flagship Seeding...\n";

// =========================================================================
// 1. UPDATE EQUIPMENT SPECIFICATIONS, STATUS & FATE
// =========================================================================

$equipmentUpdates = [
    // 1. CURIOSITY ROVER (id: 8, mission: 8)
    8 => [
        'name' => 'Curiosity Rover',
        'official_name' => 'Mars Science Laboratory (MSL) Curiosity Rover',
        'slug' => 'curiosity',
        'type' => 'Mars Exploration Rover',
        'mass' => '899 kg (Earth mass) / Science payload: 75 kg',
        'dimensions' => '3.0 m × 2.8 m × 2.1 m (Mast height)',
        'power' => 'MMRTG (Multi-Mission Radioisotope Thermoelectric Generator, ~110 W electrical at launch, Plutonium-238 oxide) + 2x Lithium-ion 43 Ah batteries',
        'mobility' => '6-wheel rocker-bogie suspension, machined aluminum wheels with chevron grousers, independent electric steering on 4 corner wheels; maximum design speed 0.16 km/h; >32 km traversed',
        'robotic_arm' => '2.1 m 5-degree-of-freedom robotic arm with cross-axis turret carrying percussive drill (powder extraction down to 5 cm depth), Dust Removal Tool (DRT), APXS, and MAHLI imager',
        'autonomy' => 'Autonomous Hazard Avoidance (AutoNav) using stereo hazard cameras (Hazcams) and visual odometry for self-directed path calculation',
        'sample_caching' => 'CHIMRA (Collection and Handling for Interior Martian Rock Analysis) sieving and powder distribution mechanism supplying SAM and CheMin internal analytical laboratories',
        'purpose' => 'Assess the past and present habitability of Gale Crater by determining whether the ancient lake environment ever possessed chemical building blocks and energy sources suitable for microbial life.',
        'technology' => 'Entry, Descent, and Landing (EDL) guided entry with 21.5-meter supersonic parachute and rocket-powered Sky Crane descent stage tether touchdown.',
        'communication' => 'Direct-to-Earth X-band high-gain and low-gain antennas; UHF transceiver relaying telemetry via Mars Reconnaissance Orbiter and Odyssey at up to 2 Mbps.',
        'operational_period' => '2012–Present (Over 12 Earth Years / 4,400+ Martian Sols)',
        'journey_days' => 254,
        'current_status' => 'OPERATIONAL',
        'current_location' => 'Gediz Vallis / Mount Sharp (Aeolis Mons), Gale Crater, Mars',
        'primary_region' => 'Mount Sharp (Aeolis Mons) foothills & Gediz Vallis channel',
        'mission_phase' => 'Extended Science Operations (Aeolis Mons Ascent)',
        'is_relic' => 0,
        'relic_category' => 'Operational Off-World Laboratory',
        'description' => 'Curiosity is NASA\'s car-sized robotic explorer that touched down inside the 154-kilometer-wide Gale Crater in August 2012. Powered by a radioisotope thermoelectric generator, it has scaled the lower layers of Mount Sharp, reading the sedimentary rock layers like pages in a planetary history book to uncover when and how Mars transitioned from a wet, potentially habitable world into an arid desert.',
        'discoveries' => "Discovered an ancient freshwater lake bed in Yellowknife Bay containing all key biogenic elements (carbon, hydrogen, nitrogen, oxygen, phosphorus, and sulfur);\nDetected indigenous organic macromolecules preserved inside 3.5-billion-year-old mudstone;\nDetected cyclical background methane variations in Mars atmosphere;\nUncovered evidence of ancient hydrothermal activity and manganese-oxide minerals indicating high-oxygen groundwater episodes;\nDocumented liquid water stream beds with rounded gravel pebbles.",
        'fate' => 'Actively traversing the upper sulfates of Mount Sharp; MMRTG power continues to generate sufficient wattage for daily science operations through the late 2020s.',
        'legacy' => 'Pioneered the Sky Crane landing architecture that made Perseverance possible; fundamentally proved that ancient Mars once possessed persistent habitable lake and river environments suitable for microbial life.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 2. OPPORTUNITY ROVER (id: 7, mission: 7)
    7 => [
        'name' => 'Opportunity Rover',
        'official_name' => 'Mars Exploration Rover B (MER-B / Opportunity)',
        'slug' => 'opportunity',
        'type' => 'Mars Exploration Rover',
        'mass' => '185 kg (Earth mass)',
        'dimensions' => '1.6 m × 2.3 m × 1.5 m',
        'power' => 'Triple-junction GaAs solar arrays (140 W peak clean sol, declining to ~100 W during dust seasons) + 2x Lithium-ion 8 Ah batteries',
        'mobility' => '6-wheel rocker-bogie system with individual wheel drive; spiral flexure cleats; 45.16 km total off-world driving distance record',
        'robotic_arm' => '3-joint Instrument Deployment Device (IDD) arm carrying Mössbauer spectrometer, APXS, Microscopic Imager, and Rock Abrasion Tool',
        'autonomy' => 'Local stereo hazard avoidance algorithm (Hazcams) and visual odometry with ground-commanded waypoint navigation',
        'sample_caching' => 'Rock Abrasion Tool (RAT) diamond-impregnated grinding bits for grinding fresh surface rock faces (in-situ analysis only)',
        'purpose' => 'Search for and characterize a wide range of rocks and soils that hold clues to past water activity on the plains of Meridiani Planum.',
        'technology' => 'Airbag cluster cushioned landing inside an impact crater, surrounded by protective tetrahedral lander petals.',
        'communication' => 'High-gain steerable X-band dish and low-gain antenna for direct-to-Earth; UHF antenna for orbital relay via Mars Odyssey and MRO.',
        'operational_period' => '2004–2018 (5,111 Martian Sols / 14 Earth Years 138 Days)',
        'journey_days' => 201,
        'current_status' => 'INACTIVE',
        'current_location' => 'Perseverance Valley, western rim of Endeavour Crater, Meridiani Planum, Mars',
        'primary_region' => 'Endeavour Crater / Meridiani Planum',
        'mission_phase' => 'Mission Concluded (Relic)',
        'is_relic' => 1,
        'relic_category' => 'Planetary Surface Relic',
        'description' => 'Opportunity touched down on Mars in January 2004 for a planned 90-day mission. Defying all expectations, the solar-powered rover operated for nearly 15 Earth years (5,111 Martian sols), traversing 45.16 kilometers across Meridiani Planum and uncovering undeniable evidence that liquid water once flowed across Mars.',
        'discoveries' => "Discovered hematite-rich spherules ('Martian blueberries') proving past standing acidic water;\nUncovered sulfate mineral veins in Endeavour Crater indicating neutral-pH ancient water;\nCompleted the first off-world marathon distance (42.195 km) in March 2015;\nExplored Eagle, Endurance, Victoria, and Endeavour craters revealing vast stratified geological records.",
        'fate' => 'Encountered a planet-encircling catastrophic global dust storm in June 2018 at Perseverance Valley. Atmospheric opacity reached record highs (tau > 10.8), cutting off solar illumination. The rover entered low-power sleep and never woke up. NASA officially declared the mission complete on 13 February 2019.',
        'legacy' => 'Holds the all-time off-world distance driving record (45.16 km); proved the long-term viability of robotic field geology on other planets; transformed humanity\'s understanding of Martian paleoclimates.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 3. SPIRIT ROVER (id: 6, mission: 6)
    6 => [
        'name' => 'Spirit Rover',
        'official_name' => 'Mars Exploration Rover A (MER-A / Spirit)',
        'slug' => 'spirit',
        'type' => 'Mars Exploration Rover',
        'mass' => '185 kg (Earth mass)',
        'dimensions' => '1.6 m × 2.3 m × 1.5 m',
        'power' => 'Triple-junction GaAs solar arrays (~140 W peak) + 2x Lithium-ion batteries',
        'mobility' => '6-wheel rocker-bogie mechanism; 7.73 km total distance traversed; right-front wheel jammed in 2006, dragged in reverse',
        'robotic_arm' => '3-joint Instrument Deployment Device (IDD) arm carrying Mössbauer spectrometer, APXS, Microscopic Imager, and RAT',
        'autonomy' => 'Stereo-vision hazard detection and local avoidance odometry',
        'sample_caching' => 'Rock Abrasion Tool (RAT) grinding head for exposing unweathered rock interiors',
        'purpose' => 'Determine whether the vast basin of Gusev Crater once held an ancient lake fed by the Ma\'adim Vallis canyon network.',
        'technology' => 'Parachute, retro-rockets, and airbag impact attenuation system inside a tetrahedral lander chassis.',
        'communication' => 'Direct-to-Earth X-band high-gain and low-gain antennas; UHF orbital relay via Mars Odyssey.',
        'operational_period' => '2004–2010 (2,210 Martian Sols / 6 Earth Years 77 Days)',
        'journey_days' => 208,
        'current_status' => 'INACTIVE',
        'current_location' => 'West side of Home Plate plateau, Gusev Crater, Mars',
        'primary_region' => 'Columbia Hills / Gusev Crater',
        'mission_phase' => 'Mission Concluded (Relic)',
        'is_relic' => 1,
        'relic_category' => 'Planetary Surface Relic',
        'description' => 'Spirit was the first of the twin Mars Exploration Rovers to arrive at the Red Planet, touching down inside the volcanic basin of Gusev Crater in January 2004. Defying its planned 90-sol design lifetime, Spirit explored for over 6 Earth years, climbing the Columbia Hills and uncovering ancient hydrothermal hot spring deposits.',
        'discoveries' => "Discovered 90% pure opaline silica soils churned up by its frozen right-front wheel, proving past volcanic hydrothermal fumaroles or hot springs;\nDiscovered iron-magnesium carbonate outcrops at Comanche, proving an ancient neutral-pH water environment under a thick carbon-dioxide atmosphere;\nScaled Husband Hill summit (107 meters elevation).",
        'fate' => 'Became trapped in soft sulfate-rich sands (Troy) on the west side of Home Plate in May 2009. The rover operated as a stationary science platform through the Martian winter. The final transmission was received on 22 March 2010. NASA formally concluded recovery operations on 25 May 2011.',
        'legacy' => 'Demonstrated extreme resilience in planetary exploration; proved ancient Mars had active volcanic hydrothermal systems capable of preserving biological signatures; operated for 2,210 sols (over 20 times its planned warranty).',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 4. APOLLO 15 LUNAR ROVING VEHICLE (id: 2, mission: 2)
    2 => [
        'name' => 'Apollo 15 Lunar Roving Vehicle',
        'official_name' => 'Lunar Roving Vehicle LRV-001',
        'slug' => 'apollo-15-lrv',
        'type' => 'Crewed Lunar Rover',
        'mass' => '210 kg (empty Earth mass) / 35 kg on Moon; 700 kg fully laden with 2 astronauts and gear',
        'dimensions' => '3.1 m length × 1.8 m width × 1.1 m height (wheelbase: 2.3 m)',
        'power' => 'Two 36-volt non-rechargeable Silver-Zinc potassium hydroxide batteries (121 Ah capacity each, ~8.7 kWh total energy)',
        'mobility' => '4 independent DC electric traction motors (1/4 hp per wheel, 10,000 rpm geared 80:1 Harmonic Drive); woven zinc-coated steel wire mesh chevron tires with titanium treads; top speed 13 km/h; 27.76 km traversed',
        'robotic_arm' => 'None (Astronaut manual operation using geological tongs, core drills, extension handles, and scoops)',
        'autonomy' => 'None (Astronaut manual operation via central T-handle joystick controller; dead-reckoning directional gyro odometer computer)',
        'sample_caching' => 'Astronaut-operated sample collection bags, core sample tubes, and stowage containers mounted on the aft chassis',
        'purpose' => 'Expand the exploration radius and payload capacity of Apollo astronauts across the rugged Hadley-Apennine lunar surface.',
        'technology' => 'Folded into Lunar Module Falcon descent stage Quadrant 1, deployed autonomously via astronaut lanyard pulleys in under 15 minutes.',
        'communication' => 'Lunar Communications Relay Unit (LCRU) with high-gain S-band parabolic antenna for direct-to-Earth color TV and VHF low-gain antenna for astronaut suit comms.',
        'operational_period' => '31 July – 02 August 1971 (3 Extravehicular Activities / 18 hr 30 min drive time)',
        'journey_days' => 4,
        'current_status' => 'ABANDONED',
        'current_location' => 'Hadley-Apennine (26.1322° N, 3.6339° E), Mare Imbrium boundary, Moon',
        'primary_region' => 'Hadley Rille & Apennine Mountain Front',
        'mission_phase' => 'Mission Completed (Lunar Relic)',
        'is_relic' => 1,
        'relic_category' => 'Lunar Surface Relic',
        'description' => 'The Apollo 15 Lunar Roving Vehicle (LRV-001) was the first wheeled vehicle driven by humans on another celestial body. Built by Boeing and Delco Electronics, it allowed astronauts David Scott and James Irwin to venture 5 kilometers away from the Lunar Module Falcon, covering 27.76 kilometers across the Hadley-Apennine valley.',
        'discoveries' => "Enabled astronauts to climb the slopes of Mt. Hadley Delta and discover the 'Genesis Rock' (Sample 15415, anorthosite dated to 4.1 billion years, confirming the lunar magma ocean hypothesis);\nCollected 77 kg of lunar rock and soil samples;\nExplored the 300-meter-deep chasm of Hadley Rille.",
        'fate' => 'Parked at the "VIP site" 90 meters east of LM Falcon at the conclusion of EVA 3. Its ground-commanded RCA color television camera broadcast the Falcon ascent stage liftoff into lunar orbit. The rover remains permanently parked on the lunar surface, preserved in the pristine vacuum of the Moon.',
        'legacy' => 'Revolutionized lunar field geology by expanding surface exploration distance by a factor of ten; paved the way for all subsequent off-world rovers.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 5. VOYAGER 1 SPACECRAFT (id: 3, mission: 3)
    3 => [
        'name' => 'Voyager 1 Spacecraft',
        'official_name' => 'Voyager 1 Interstellar Mission',
        'slug' => 'voyager-1',
        'type' => 'Interstellar Flyby Spacecraft',
        'mass' => '825.5 kg (Launch mass including 105 kg science instruments)',
        'dimensions' => '3.7 m diameter parabolic High-Gain Antenna dish; 13 m magnetometer boom',
        'power' => '3 Radioisotope Thermoelectric Generators (RTGs, Plutonium-238 oxide, initially 470 W, currently producing ~220 W electrical)',
        'mobility' => 'Interstellar hyperbolic solar escape trajectory at ~17 km/s (~38,000 mph, ~3.6 AU/year)',
        'robotic_arm' => 'None (Passive instrument booms and scan platform)',
        'autonomy' => 'Onboard Fault Protection and command-loss timer routines with dual Computer Command System (CCS)',
        'sample_caching' => 'None (Carries the 12-inch Gold-Plated Copper Interstellar Phonograph Record)',
        'purpose' => 'Conduct close-range reconnaissance of the Jupiter and Saturn planetary systems, then venture beyond the solar heliosphere into interstellar space.',
        'technology' => 'Three-axis stabilized using Sun sensor and Canopus star tracker; hydrazine thrusters; 3.7-meter High-Gain Antenna communicating via S-band and X-band.',
        'communication' => '3.7 m High-Gain Antenna transmitting on X-band (8.4 GHz) and S-band; signal travels over 22.5 light-hours each way to NASA Deep Space Network 70-meter dishes.',
        'operational_period' => '1977–Present (48+ Years of Continuous Interstellar Flight)',
        'journey_days' => 17740,
        'current_status' => 'OPERATIONAL',
        'current_location' => 'Local Interstellar Medium (>163 AU / 24.5 billion km from Sun, constellation Ophiuchus)',
        'primary_region' => 'Interstellar Space beyond the Heliopause',
        'mission_phase' => 'Voyager Interstellar Mission (VIM)',
        'is_relic' => 1,
        'relic_category' => 'Interstellar Artifact',
        'description' => 'Launched in September 1977, Voyager 1 completed historic flybys of Jupiter (1979) and Saturn (1980) before heading outward along a hyperbolic trajectory out of the ecliptic plane. In August 2012, Voyager 1 crossed the Heliopause to become the first human-made object to enter interstellar space. It is the most distant operational machine in human history.',
        'discoveries' => "Discovered active sulfur volcanism on Jupiter’s moon Io (the first active volcanism confirmed beyond Earth);\nDiscovered two new moons of Jupiter (Thebe and Metis) and a faint Jovian ring system;\nDiscovered five new moons of Saturn and revealed intricate ringlet structures;\nCaptured the legendary 'Pale Blue Dot' portrait of Earth from 6 billion km away;\nDetected the sharp boundary of the Heliopause and measured the density of interstellar plasma directly.",
        'fate' => 'Currently transmitting scientific data from the interstellar medium across a one-way light time exceeding 22.5 hours. Power from its decaying plutonium-238 RTGs is carefully managed by turning off non-essential heaters; expected to operate at least one instrument until ~2025–2030, after which it will coast silently through the Milky Way for millions of years.',
        'legacy' => 'Humanity’s premier interstellar emissary; carries the Golden Record with greetings in 55 languages and sounds of Earth; redefined our place in the cosmos.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 6. VIKRAM LANDER (id: 14, mission: 14)
    14 => [
        'name' => 'Vikram Lander',
        'official_name' => 'Chandrayaan-3 Vikram Lander',
        'slug' => 'chandrayaan-3-vikram',
        'type' => 'Lunar Polar Soft Lander',
        'mass' => '1,752 kg (including 26 kg Pragyan rover and propellant)',
        'dimensions' => '2.0 m × 2.0 m × 1.17 m',
        'power' => 'Side-mounted solar panels (738 W electrical generation) + Li-Ion storage battery',
        'mobility' => 'Stationary lander chassis with four shock-absorbing aluminum legs; executed autonomous 40 cm sub-hop re-ignition on 03 Sep 2023',
        'robotic_arm' => 'None (Ramp deployment mechanism for Pragyan rover; ChaSTE thermal penetration probe)',
        'autonomy' => 'Lander Hazard Detection and Avoidance (LHDA) system with real-time velocity camera and processing unit',
        'sample_caching' => 'None (In-situ physical and seismic regolith measurements)',
        'purpose' => 'Demonstrate safe and soft landing capability near the lunar south polar region and conduct in-situ scientific measurements of the high-latitude regolith and plasma.',
        'technology' => 'Four 800 N throttleable liquid engines, laser altimeters, and hazard-avoidance cameras enabling autonomous touchdown.',
        'communication' => 'Direct-to-Earth X-band communication to Indian Deep Space Network (IDSN) at Byalalu; secondary relay through Chandrayaan-2 orbiter.',
        'operational_period' => '23 August – 04 September 2023 (1 Lunar Day / 14 Earth Days)',
        'journey_days' => 40,
        'current_status' => 'COMPLETED',
        'current_location' => 'Shiv Shakti Point (69.3676° S, 32.3481° E), Lunar South Pole region, Moon',
        'primary_region' => 'High Southern Lunar Latitude / Shiv Shakti Point',
        'mission_phase' => 'Mission Completed (Lunar South Pole Relic)',
        'is_relic' => 1,
        'relic_category' => 'Lunar Surface Relic',
        'description' => 'Vikram is the ISRO lander that achieved humanity’s first successful soft landing in the lunar south polar region on 23 August 2023. Touchdown occurred at Shiv Shakti Point (69.37° S). Named after Dr. Vikram Sarabhai, the lander operated for one lunar day, deploying the Pragyan rover and executing a historic hop experiment before entering planned sleep.',
        'discoveries' => "Measured the temperature profile of lunar topsoil using the ChaSTE thermal probe, discovering a dramatic 60°C gradient between the surface (~50°C) and just 8 cm below (-10°C);\nRecorded lunar seismic activity using the ILSA seismometer, including signals from rover movement and micro-meteoroid impacts;\nDemonstrated in-situ engine re-ignition and a 40-centimeter hop test essential for future sample return missions.",
        'fate' => 'Completed all primary scientific objectives during the 14-day lunar day. On 04 September 2023, Vikram’s payloads were shut down and receivers kept active as lunar night arrived (-180°C). Remains preserved permanently as a historic relic at Shiv Shakti Point.',
        'legacy' => 'Made India the fourth country to soft-land on the Moon and the first to land near the lunar south pole; proved high-precision autonomous hazard-avoidance landing technology.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 7. PRAGYAN ROVER (id: 15, mission: 14)
    15 => [
        'name' => 'Pragyan Rover',
        'official_name' => 'Chandrayaan-3 Pragyan Rover',
        'slug' => 'chandrayaan-3-pragyan',
        'type' => 'Lunar Exploration Rover',
        'mass' => '26 kg',
        'dimensions' => '0.91 m × 0.75 m × 0.39 m',
        'power' => '50 W solar panel generation + battery',
        'mobility' => '6-wheel rocker-bogie mechanism with custom rim treads carrying ISRO emblem and National Emblem embossments; 101.4 meters traversed',
        'robotic_arm' => 'None (Chassis-mounted deployable instrument bracket)',
        'autonomy' => 'Stereo-camera visual odometry with ground hazard avoidance and autonomous course corrections',
        'sample_caching' => 'None (Contact and remote spectroscopy of untouched polar regolith)',
        'purpose' => 'Conduct in-situ chemical and elemental analysis of the lunar surface rocks and soil around the landing site.',
        'technology' => 'Two-ramp egress deployment from Vikram lander deck; direct Wi-Fi/RF link to Vikram lander.',
        'communication' => 'Dedicated RF link directly to Vikram lander transceiver at 2.4 GHz; telemetry relayed to Earth via Vikram.',
        'operational_period' => '24 August – 02 September 2023 (10 Earth Days)',
        'journey_days' => 40,
        'current_status' => 'COMPLETED',
        'current_location' => 'Shiv Shakti Point, high southern latitude, Moon',
        'primary_region' => 'Shiv Shakti Point / Lunar South Pole',
        'mission_phase' => 'Mission Completed (Lunar South Pole Relic)',
        'is_relic' => 1,
        'relic_category' => 'Lunar Surface Relic',
        'description' => 'Pragyan is the 26-kilogram lunar rover that rolled down Vikram’s ramp onto the lunar south polar surface on 23 August 2023. Over the course of 10 Earth days, it traversed 101.4 meters, avoiding craters and conducting the first direct spectroscopy of untouched south polar regolith.',
        'discoveries' => "Unambiguously confirmed the presence of Sulfur (S) in the high southern lunar regolith using the Laser-Induced Breakdown Spectroscope (LIBS);\nDetected aluminum, calcium, iron, chromium, titanium, manganese, silicon, and oxygen;\nTraversed 101.4 meters while navigating around obstacles including a 4-meter-diameter crater.",
        'fate' => 'Safely parked in a sunlit location and put into sleep mode on 02 September 2023 with solar panel oriented to receive light at sunrise. The receiver was kept on, but the rover remained dormant through the deep cold of lunar night. Remains parked on the lunar regolith.',
        'legacy' => 'First rover to operate near the lunar south pole; provided unprecedented ground-truth geochemical data validating satellite spectral observations.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 8. HAYABUSA2 SPACECRAFT (id: 16, mission: 15)
    16 => [
        'name' => 'Hayabusa2 Spacecraft',
        'official_name' => 'Hayabusa2 Asteroid Sample Return & Extended Mission',
        'slug' => 'hayabusa2',
        'type' => 'Asteroid Sample Return Spacecraft',
        'mass' => '609 kg (Launch mass including 66 kg propellant)',
        'dimensions' => '1.0 m × 1.6 m × 1.25 m (solar array span: 6.0 m)',
        'power' => 'GaAs triple-junction solar arrays generating ~2.6 kW at 1 AU + ion thruster bus',
        'mobility' => '4 µ10 microwave discharge ion thrusters generating 28 mN thrust each, with hydrazine RCS thrusters',
        'robotic_arm' => 'Sampler Horn (1 m deployable cylinder firing 5-gram tantalum bullets at 300 m/s)',
        'autonomy' => 'Autonomous target marker optical tracking, laser range-finding, and touchdown thruster abort logic',
        'sample_caching' => 'Hermetically sealed sample capsule with 3 independent chambers for surface and subsurface asteroid grains',
        'purpose' => 'Rendezvous with carbonaceous C-type asteroid 162173 Ryugu, map its surface, perform kinetic impact excavation, collect surface and subsurface samples, and return them safely to Earth.',
        'technology' => 'Ion propulsion, kinetic impact projectile (SCI), and deployable autonomous mini-rovers (MINERVA-II) and lander (MASCOT).',
        'communication' => 'High-gain parabolic antenna communicating via X-band and Ka-band to Usuda Deep Space Center and JAXA stations.',
        'operational_period' => '2014–Present (Extended Mission active through 2031)',
        'journey_days' => 1302,
        'current_status' => 'OPERATIONAL',
        'current_location' => 'Interplanetary Space (En route to Asteroid 1998 KY26, rendezvous July 2031)',
        'primary_region' => 'Asteroid 162173 Ryugu (Primary) / Interplanetary Heliocentric Orbit (Extended)',
        'mission_phase' => 'Hayabusa2# Extended Mission (Cruise to Asteroid 1998 KY26)',
        'is_relic' => 0,
        'relic_category' => 'Operational Deep-Space Laboratory',
        'description' => 'Hayabusa2 is JAXA’s flagship asteroid explorer. Launched in December 2014, it arrived at C-type near-Earth asteroid Ryugu in June 2018. Over 18 months of proximity operations, it deployed multiple surface rovers, created the first artificial impact crater on an asteroid using an explosive projectile, gathered 5.4 grams of pristine samples, and successfully delivered them to the Australian outback in December 2020.',
        'discoveries' => "Discovered that Ryugu is a loose rubble-pile asteroid with very low tensile strength;\nCreated a 10-meter artificial impact crater on Ryugu using the Small Carry-on Impactor (SCI);\nDelivered 5.4 grams of pristine carbonaceous regolith to Earth;\nScientific laboratories detected over 20 amino acids, liquid water inclusions, and uracil (an RNA building block) in the returned grains, demonstrating that carbonaceous asteroids contributed prebiotic compounds to early Earth.",
        'fate' => 'After dropping off the sample return capsule in Earth’s atmosphere on 06 December 2020, Hayabusa2 ignited its ion engines to enter an extended mission (Hayabusa2#). It flew past asteroid 2001 CC21 in 2026 and is scheduled to rendezvous with fast-rotating asteroid 1998 KY26 in July 2031.',
        'legacy' => 'Cemented sample-return technology as the gold standard for planetary science; delivered the purest organic-rich samples of the early solar system ever studied on Earth.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 9. ROSETTA SPACECRAFT (id: 17, mission: 16)
    17 => [
        'name' => 'Rosetta Spacecraft',
        'official_name' => 'Rosetta Cometary Orbiter',
        'slug' => 'rosetta',
        'type' => 'Cometary Orbiter Spacecraft',
        'mass' => '3,000 kg (Launch mass including 1,670 kg fuel and 100 kg Philae lander)',
        'dimensions' => '2.8 m × 2.1 m × 2.0 m central body with two 14-meter solar wings (32 m total wingspan)',
        'power' => 'Two 14 m solar wings with high-efficiency silicon cells producing 850 W at 3.4 AU from Sun',
        'mobility' => 'Bi-propellant thruster system; 10-year cruise covering 6.4 billion km with 3 Earth and 1 Mars gravity assists',
        'robotic_arm' => 'None (11 onboard remote-sensing spectrometers, imagers, and dust analyzers)',
        'autonomy' => 'Comet-environment autonomous navigation relying on star trackers and optical comet limb tracking',
        'sample_caching' => 'None (Carried the deployable Philae lander; in-situ cometary dust collection via MIDAS and COSIMA)',
        'purpose' => 'Rendezvous with and accompany comet 67P/Churyumov-Gerasimenko along its orbit around the Sun, deploy the Philae lander, and map the evolution of a cometary nucleus.',
        'technology' => 'First spacecraft to orbit a comet and track its transformation through perihelion; high-efficiency low-light solar arrays.',
        'communication' => '2.2 m steerable parabolic High-Gain Antenna communicating via S-band and X-band to ESA ESTRACK network.',
        'operational_period' => '2004–2016 (12 Years 212 Days)',
        'journey_days' => 3809,
        'current_status' => 'INACTIVE',
        'current_location' => 'Ma’at region pit walls, Comet 67P/Churyumov-Gerasimenko',
        'primary_region' => 'Comet 67P/Churyumov-Gerasimenko',
        'mission_phase' => 'Mission Completed (Controlled Impact Relic)',
        'is_relic' => 1,
        'relic_category' => 'Cometary Surface Relic',
        'description' => 'Rosetta was the landmark European Space Agency mission that achieved the first orbit around a comet nucleus. Launched in 2004, it travelled for a decade across 6.4 billion kilometers before rendezvousing with comet 67P/Churyumov-Gerasimenko in August 2014. It deployed the Philae lander and studied the comet for two years as it rounded the Sun.',
        'discoveries' => "ROSINA spectrometer determined that the deuterium-to-hydrogen (D/H) ratio in comet 67P’s water vapor is three times greater than that of Earth’s oceans, showing comets of this family were not the primary source of Earth’s water;\nDiscovered glycine (an amino acid) and phosphorus (key to DNA/cell membranes) in the comet coma;\nDiscovered molecular oxygen (O2) outgassing in high abundances (~3.8% relative to water);\nMapped the duck-shaped bilobate nucleus structure.",
        'fate' => 'On 30 September 2016, Rosetta executed a controlled descent into the Ma’at region of Comet 67P, collecting high-resolution close-up imagery and gas measurements down to the surface before shutting down transmitters upon impact. It rests permanently on the cometary nucleus.',
        'legacy' => 'A triumph of European space engineering; completely transformed cometary science and planetary formation models.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ],

    // 10. PHILAE LANDER (id: 18, mission: 16)
    18 => [
        'name' => 'Philae Lander',
        'official_name' => 'Philae Cometary Lander',
        'slug' => 'philae',
        'type' => 'Cometary Surface Lander',
        'mass' => '100 kg (including 26.7 kg scientific instruments)',
        'dimensions' => '0.8 m × 0.85 m × 0.85 m (tripod landing gear span: 1.0 m)',
        'power' => 'Primary battery (60 hours operation) + solar cells recharging secondary battery',
        'mobility' => 'Stationary lander; performed two unplanned ballistic bounces across 2 hours due to harpoon non-firing',
        'robotic_arm' => 'SD2 (Sample, Drill and Distribution) drill mechanism and MUPUS penetrator hammering boom',
        'autonomy' => 'Pre-programmed autonomous 60-hour first-science sequence executed after separation',
        'sample_caching' => 'Carousel oven system for heating and pyrolyzing drilled cometary samples for COSAC gas analysis',
        'purpose' => 'Achieve the first soft touchdown on a comet nucleus, anchor to the surface, and analyze the organic and physical properties of the cometary crust.',
        'technology' => 'Cold gas hold-down thruster, two ice harpoons, three ice screws on landing feet, and flywheel attitude control.',
        'communication' => 'Dedicated S-band transponder communicating directly with Rosetta orbiter overhead.',
        'operational_period' => '12–15 November 2014 (Primary 64-Hour Science Sequence; brief contact in June 2015)',
        'journey_days' => 3908,
        'current_status' => 'INACTIVE',
        'current_location' => 'Abydos crevice, beneath a rocky overhang, Comet 67P/Churyumov-Gerasimenko',
        'primary_region' => 'Abydos / Comet 67P/Churyumov-Gerasimenko',
        'mission_phase' => 'Mission Completed (Cometary Relic)',
        'is_relic' => 1,
        'relic_category' => 'Cometary Surface Relic',
        'description' => 'Philae was the robotic lander deployed by Rosetta on 12 November 2014. After descending for 7 hours, its anchoring harpoons and thruster failed to fire, causing it to bounce twice off the comet nucleus before coming to rest in the shadowed crevice of Abydos. It completed 80% of its planned primary science sequence on battery power before entering hibernation.',
        'discoveries' => "Achieved the first soft touchdown on a comet nucleus;\nCOSAC gas chromatograph identified 16 organic compounds including four nitrogen-bearing molecules (methyl isocyanate, acetone, propionaldehyde, and acetamide) never before seen on comets;\nMUPUS hammer penetrator proved the cometary ice-dust crust beneath the surface dust layer was as hard as solid rock (tensile strength >2 MPa);\nROMAP magnetometer confirmed the comet nucleus is completely unmagnetized.",
        'fate' => 'Operated for 64 hours on primary battery power before entering hibernation on 15 November 2014. As the comet approached the Sun in June 2015, solar panels received enough illumination for brief communications. Located on 02 September 2016 by Rosetta’s OSIRIS camera wedged under a boulder at Abydos. Permanently preserved on Comet 67P.',
        'legacy' => 'First spacecraft to touch down on a cometary nucleus; delivered ground-truth measurements of primordial solar system chemistry.',
        'verification_status' => 'CONFIRMED',
        'last_verified' => '2026-03-20'
    ]
];

$upStmt = $pdo->prepare("
    UPDATE equipment SET
        name = :name, official_name = :official_name, slug = :slug, type = :type,
        mass = :mass, dimensions = :dimensions, power = :power, mobility = :mobility,
        robotic_arm = :robotic_arm, autonomy = :autonomy, sample_caching = :sample_caching,
        purpose = :purpose, technology = :technology, communication = :communication,
        operational_period = :operational_period, journey_days = :journey_days,
        current_status = :current_status, current_location = :current_location,
        primary_region = :primary_region, mission_phase = :mission_phase,
        is_relic = :is_relic, relic_category = :relic_category,
        description = :description, discoveries = :discoveries, fate = :fate,
        legacy = :legacy, verification_status = :verification_status, last_verified = :last_verified
    WHERE id = :id
");

foreach ($equipmentUpdates as $id => $fields) {
    $fields['id'] = $id;
    $upStmt->execute($fields);
    echo "Updated Equipment #$id ({$fields['name']})\n";
}

// =========================================================================
// 2. NORMALIZED SCIENTIFIC INSTRUMENTS
// =========================================================================

$instruments = [
    // --- CURIOSITY (Equipment #8) ---
    [
        'name' => 'Mastcam',
        'official_name' => 'Mast Camera Stereoscopic Imaging System',
        'type' => 'Multispectral Stereoscopic Camera',
        'purpose' => 'Capture color, stereo, and panoramic images and video of the Martian terrain, geologic features, and atmospheric dust.',
        'description' => 'Two camera systems mounted on Curiosity\'s remote sensing mast with 34 mm and 100 mm focal length lenses providing multispectral visible and near-infrared imaging.',
        'manufacturer' => 'Malin Space Science Systems',
        'agency_id' => 1,
        'specifications' => 'Mass: 2.8 kg | Power: 13 W | Resolution: 1600x1200 pixels | Filter positions: 8 per camera',
        'equip_id' => 8
    ],
    [
        'name' => 'ChemCam',
        'official_name' => 'Chemistry and Camera Laser-Induced Breakdown Spectrometer',
        'type' => 'Laser Spectrometer & Micro-Imager',
        'purpose' => 'Rapidly determine elemental composition of rocks and soils from up to 7 meters away by vaporizing microscopic spots with laser pulses.',
        'description' => 'Fires 1067 nm infrared laser pulses onto targets to create an ionized plasma plume, recording emission spectra across 240–853 nm wavelengths.',
        'manufacturer' => 'Los Alamos National Laboratory / CNES (France)',
        'agency_id' => 1,
        'specifications' => 'Mass: 5.6 kg | Laser Energy: 14 mJ per pulse | Spectral Range: 240–853 nm | Range: 1.2 to 7 meters',
        'equip_id' => 8
    ],
    [
        'name' => 'SAM',
        'official_name' => 'Sample Analysis at Mars Instrument Suite',
        'type' => 'Analytical Chemistry Laboratory',
        'purpose' => 'Search for carbon-containing organic compounds, measure light isotope ratios, and determine atmospheric composition.',
        'description' => 'Contains a Quadrupole Mass Spectrometer (QMS), Gas Chromatograph (GC), and Tunable Laser Spectrometer (TLS) fed by a 74-cup pyrolysis carousel heating samples up to 1,000°C.',
        'manufacturer' => 'NASA Goddard Space Flight Center / CNES',
        'agency_id' => 1,
        'specifications' => 'Mass: 40 kg | Power: 80 W | 74 sample cups | Oven range: ambient to 1,000°C',
        'equip_id' => 8
    ],
    [
        'name' => 'CheMin',
        'official_name' => 'Chemistry and Mineralogy X-Ray Powder Diffraction Instrument',
        'type' => 'X-Ray Diffraction Spectrometer',
        'purpose' => 'Identify and quantify specific mineral phases in drilled rocks and scooped soils.',
        'description' => 'Directs a collimated beam of cobalt X-rays through sieved mineral powder, producing diffraction rings on a CCD detector to identify crystallographic mineral structure.',
        'manufacturer' => 'NASA Ames Research Center',
        'agency_id' => 1,
        'specifications' => 'Mass: 10 kg | Power: 40 W | Co X-ray source (28 keV) | Angular range: 2° to 50° 2-theta',
        'equip_id' => 8
    ],
    [
        'name' => 'APXS (MSL)',
        'official_name' => 'Alpha Particle X-Ray Spectrometer',
        'type' => 'Elemental Contact Spectrometer',
        'purpose' => 'Measure abundance of major, minor, and trace chemical elements in rocks and soils in direct physical contact.',
        'description' => 'Bombards target samples with alpha particles and X-rays from Curium-244 isotopes, detecting backscattered X-ray emissions to identify elements from sodium to strontium.',
        'manufacturer' => 'Canadian Space Agency / University of Guelph',
        'agency_id' => 1,
        'specifications' => 'Mass: 1.8 kg | Sensor head: 80 mm diameter | Radioactive source: Curium-244',
        'equip_id' => 8
    ],
    [
        'name' => 'MAHLI',
        'official_name' => 'Mars Hand Lens Imager',
        'type' => 'Color Microscopic Camera',
        'purpose' => 'Acquire microscopic, true-color images of rock coatings, grains, textures, and frost.',
        'description' => 'Macro-focus color CCD camera mounted on the robotic arm turret capable of resolving features as small as 14 micrometers per pixel with white and UV LED illumination.',
        'manufacturer' => 'Malin Space Science Systems',
        'agency_id' => 1,
        'specifications' => 'Mass: 0.38 kg | Resolution: 1600x1200 | Working distance: 21 mm to infinity | Field of view: 34° to 39°',
        'equip_id' => 8
    ],
    [
        'name' => 'DAN',
        'official_name' => 'Dynamic Albedo of Neutrons',
        'type' => 'Active Pulsed Neutron Detector',
        'purpose' => 'Detect subsurface hydrogen, water, and hydrated minerals down to 1 meter depth beneath the rover.',
        'description' => 'Pulsed neutron generator fires high-energy 14 MeV neutrons into the ground and measures the time-die-away of thermalized backscattered neutrons.',
        'manufacturer' => 'Roscosmos / Space Research Institute (IKI)',
        'agency_id' => 6,
        'specifications' => 'Mass: 4.9 kg | Neutron output: 10^7 neutrons/pulse | Probing depth: ~1 meter',
        'equip_id' => 8
    ],
    [
        'name' => 'REMS',
        'official_name' => 'Rover Environmental Monitoring Station',
        'type' => 'Meteorological Sensor Suite',
        'purpose' => 'Record daily and seasonal cycles of atmospheric pressure, humidity, UV radiation, wind speed, and air/ground temperatures.',
        'description' => 'Two booms mounted on the mast measure horizontal and vertical winds, air temperature, and humidity, while deck sensors record UV flux and pressure.',
        'manufacturer' => 'Centro de Astrobiología (CAB / INTA, Spain)',
        'agency_id' => 1,
        'specifications' => 'Mass: 1.3 kg | Sampling rate: 1 Hz | Wind range: 0 to 70 m/s | Pressure accuracy: 3 Pa',
        'equip_id' => 8
    ],

    // --- OPPORTUNITY & SPIRIT (Equipment #7 and #6) ---
    [
        'name' => 'Pancam',
        'official_name' => 'Panoramic Camera System',
        'type' => 'Stereoscopic Multispectral Imager',
        'purpose' => 'Examine Martian mineralogy, atmosphere, and surface morphology in high-resolution stereo color.',
        'description' => 'Twin digital cameras mounted on the rover mast separated by 30 cm baseline with 8-position filter wheels spanning 400 to 1,000 nm.',
        'manufacturer' => 'Cornell University / Jet Propulsion Laboratory',
        'agency_id' => 1,
        'specifications' => 'Mass: 0.75 kg per camera | 1024x1024 frame transfer CCD | Angular resolution: 0.27 mrad/pixel',
        'equip_id' => 7
    ],
    [
        'name' => 'Mini-TES',
        'official_name' => 'Miniature Thermal Emission Spectrometer',
        'type' => 'Infrared Interferometer Spectrometer',
        'purpose' => 'Determine mineral composition of surrounding rocks and soils from distances up to hundreds of meters.',
        'description' => 'Michelson interferometer operating across 5 to 29 micrometers thermal infrared spectrum to identify carbonates, silicates, sulfates, and hematite.',
        'manufacturer' => 'Arizona State University',
        'agency_id' => 1,
        'specifications' => 'Mass: 2.1 kg | Spectral range: 339 to 1997 cm^-1 | Field of view: 20 mrad',
        'equip_id' => 7
    ],
    [
        'name' => 'Mössbauer Spectrometer',
        'official_name' => 'MIMOS II Mössbauer Spectrometer',
        'type' => 'Iron Mineralogy Backscatter Spectrometer',
        'purpose' => 'Determine oxidation state and mineralogical composition of iron-bearing minerals in rocks and soils.',
        'description' => 'Uses a Cobalt-57 radioactive source to excite iron-57 nuclei, measuring resonance absorption velocity spectra.',
        'manufacturer' => 'Johannes Gutenberg University Mainz (Germany)',
        'agency_id' => 1,
        'specifications' => 'Mass: 0.5 kg | Source: 57Co | Velocity drive: +/- 10 mm/s',
        'equip_id' => 7
    ],
    [
        'name' => 'RAT',
        'official_name' => 'Rock Abrasion Tool',
        'type' => 'Diamond Grinding Subsystem',
        'purpose' => 'Grind away dust and weathered exterior rock coatings to expose unweathered rock interiors for spectrometers.',
        'description' => 'High-speed diamond-matrix cutting wheels driven by electric motors to create a 45 mm diameter circular pit up to 5 mm deep.',
        'manufacturer' => 'Honeybee Robotics',
        'agency_id' => 1,
        'specifications' => 'Mass: 0.7 kg | Diameter: 45 mm | Depth: up to 5 mm | Speed: 3,000 RPM',
        'equip_id' => 7
    ],

    // --- APOLLO 15 LRV (Equipment #2) ---
    [
        'name' => 'LCRU',
        'official_name' => 'Lunar Communications Relay Unit',
        'type' => 'Autonomous S-Band / VHF Transceiver',
        'purpose' => 'Provide direct-to-Earth communications and television transmission independent of the Lunar Module.',
        'description' => 'Self-contained electronics package with steerable 3-foot parabolic high-gain antenna for live color television and low-gain helical antenna for voice and astronaut suit biotelemetry.',
        'manufacturer' => 'RCA Corporation',
        'agency_id' => 1,
        'specifications' => 'Mass: 25 kg | RF Output: S-band (2272.5 MHz) 10W / 2W | Direct line-of-sight to Earth',
        'equip_id' => 2
    ],
    [
        'name' => 'GCTA',
        'official_name' => 'Ground-Commanded Color Television Assembly',
        'type' => 'Remotely Controlled Video Camera',
        'purpose' => 'Broadcast real-time color television of lunar surface exploration and LM Falcon ascent to Mission Control.',
        'description' => 'Pan/tilt/zoom color camera controlled remotely from Houston with 6:1 zoom ratio and vidicon tube; captured the historic live ascent of Apollo 15 LM Falcon from the Moon.',
        'manufacturer' => 'RCA Corporation',
        'agency_id' => 1,
        'specifications' => 'Mass: 6.8 kg | Pan range: 360° | Tilt range: -45° to +85° | Resolution: 30 fps 525 lines',
        'equip_id' => 2
    ],
    [
        'name' => 'LRV Nav Computer',
        'official_name' => 'LRV Dead-Reckoning Navigation Subsystem',
        'type' => 'Inertial Dead-Reckoning Guidance Unit',
        'purpose' => 'Provide real-time distance, bearing, and heading back to the Lunar Module Falcon for astronaut safety.',
        'description' => 'Combines inputs from a directional gyroscope, four wheel-odometer pulse counters, and an astronaut-read sun shadow compass to calculate return vectors.',
        'manufacturer' => 'Delco Electronics / Boeing',
        'agency_id' => 1,
        'specifications' => 'Mass: 7.2 kg | Power: 10 W | Distance accuracy: within 100 meters over 10 km',
        'equip_id' => 2
    ],

    // --- VOYAGER 1 (Equipment #3) ---
    [
        'name' => 'MAG',
        'official_name' => 'Triaxial Fluxgate Magnetometer System',
        'type' => 'Magnetic Field Sensor Suite',
        'purpose' => 'Measure weak planetary, solar, and interstellar magnetic fields.',
        'description' => 'High-field and low-field triaxial fluxgate magnetometers mounted along a 13-meter deployable fiberglass boom to isolate sensors from spacecraft stray fields.',
        'manufacturer' => 'NASA Goddard Space Flight Center',
        'agency_id' => 1,
        'specifications' => 'Dynamic range: 0.01 nT to 2,000,000 nT | Boom length: 13 m | Active sensor in interstellar space',
        'equip_id' => 3
    ],
    [
        'name' => 'CRS',
        'official_name' => 'Cosmic Ray Subsystem',
        'type' => 'Energetic Particle Telescope',
        'purpose' => 'Detect high-energy cosmic rays originating from outside the solar system.',
        'description' => 'High Energy Telescope System (HETS) and Low Energy Telescope System (LETS) detecting protons, helium, and heavier nuclei up to 500 MeV/nucleon.',
        'manufacturer' => 'California Institute of Technology / Goddard',
        'agency_id' => 1,
        'specifications' => 'Energy range: 0.5 to 500 MeV/nuc | Provided definitive verification of Heliopause crossing',
        'equip_id' => 3
    ],
    [
        'name' => 'LECP',
        'official_name' => 'Low-Energy Charged Particle Detector',
        'type' => 'Charged Particle Spectrometer',
        'purpose' => 'Measure energy, composition, and angular distribution of ions and electrons.',
        'description' => 'Bi-directional rotating stepper platform with solid-state detectors measuring solar wind and interstellar boundary particles from 10 keV to 30 MeV.',
        'manufacturer' => 'Johns Hopkins Applied Physics Laboratory',
        'agency_id' => 1,
        'specifications' => 'Mass: 7.5 kg | Power: 4.2 W | Stepping cycle: 8 positions every 192 seconds',
        'equip_id' => 3
    ],
    [
        'name' => 'PWS',
        'official_name' => 'Plasma Wave Subsystem',
        'type' => 'Electric Dipole Wave Analyzer',
        'purpose' => 'Measure plasma wave oscillations, electron densities, and shockwaves in planetary magnetospheres and the interstellar medium.',
        'description' => 'Two 10-meter whip antennas mounted in a V-formation connected to a 16-channel step-frequency receiver measuring plasma oscillations from 10 Hz to 56 kHz.',
        'manufacturer' => 'University of Iowa',
        'agency_id' => 1,
        'specifications' => 'Antennas: 2x 10 m beryllium copper whips | Frequency: 10 Hz to 56 kHz | Recorded interstellar plasma sounds',
        'equip_id' => 3
    ],
    [
        'name' => 'The Golden Record',
        'official_name' => 'The Sounds of Earth Interstellar Phonograph Record',
        'type' => 'Cultural Message & Artifact Capsule',
        'purpose' => 'Preserve a message of human existence, culture, and Earth biology for potential extraterrestrial discovery.',
        'description' => '12-inch gold-plated copper phonograph disc protected by an electroplated aluminum cover containing 115 analog images, greetings in 55 languages, 90 minutes of world music, and nature sounds.',
        'manufacturer' => 'NASA / Carl Sagan Committee',
        'agency_id' => 1,
        'specifications' => 'Diameter: 30 cm | Material: Gold-plated copper with uranium-238 clock | Estimated lifetime: >1 billion years',
        'equip_id' => 3
    ],

    // --- CHANDRAYAAN-3 VIKRAM (Equipment #14) ---
    [
        'name' => 'ChaSTE',
        'official_name' => 'Chandra\'s Surface Thermophysical Experiment',
        'type' => 'Thermal Conductivity & Temperature Probe',
        'purpose' => 'Measure vertical thermal conductivity and temperature gradient across the lunar polar regolith down to 10 cm depth.',
        'description' => 'Thermal penetration probe with 10 individual precision RTD temperature sensors driven by a motorized mechanism to penetrate lunar soil.',
        'manufacturer' => 'Space Physics Laboratory (SPL / VSSC), ISRO',
        'agency_id' => 2,
        'specifications' => 'Penetration depth: up to 100 mm | Sensor count: 10 RTDs | Temperature range: -180°C to +130°C',
        'equip_id' => 14
    ],
    [
        'name' => 'ILSA',
        'official_name' => 'Instrument for Lunar Seismic Activity',
        'type' => '6-Axis MEMS Seismometer',
        'purpose' => 'Detect lunar ground acceleration and seismic events generated by natural quakes, rover motion, and meteorite impacts.',
        'description' => 'State-of-the-art silicon micro-machined MEMS sensor measuring ground vibrations with sub-nanog acceleration sensitivity.',
        'manufacturer' => 'Laboratory for Electro-Optics Systems (LEOS / ISRO)',
        'agency_id' => 2,
        'specifications' => 'Dynamic range: +/- 0.5 g | Sensitivity: 100 ng/sqrt(Hz) | Bandwidth: 0.1 to 50 Hz',
        'equip_id' => 14
    ],
    [
        'name' => 'RAMBHA-LP',
        'official_name' => 'Radio Anatomy of Moon Bound Hypersensitive Ionosphere and Atmosphere',
        'type' => 'Spherical Langmuir Probe',
        'purpose' => 'Measure near-surface lunar plasma electron density and temperature variations across the lunar day.',
        'description' => 'Deployable 5 cm spherical metallic sensor held on a 1-meter boom to measure plasma currents in the sunlit lunar boundary layer.',
        'manufacturer' => 'Space Physics Laboratory (SPL / VSSC), ISRO',
        'agency_id' => 2,
        'specifications' => 'Probe diameter: 50 mm | Bias voltage: -12V to +12V | Electron density range: 10^2 to 10^6 cm^-3',
        'equip_id' => 14
    ],

    // --- CHANDRAYAAN-3 PRAGYAN (Equipment #15) ---
    [
        'name' => 'LIBS (Pragyan)',
        'official_name' => 'Laser-Induced Breakdown Spectroscope',
        'type' => 'Laser Emission Spectrometer',
        'purpose' => 'Perform qualitative and quantitative elemental analysis of lunar rocks and soil around the landing site.',
        'description' => 'Fires high-energy Nd:YAG laser pulses onto the lunar surface to produce localized plasma, measuring characteristic emission lines to identify elemental species.',
        'manufacturer' => 'Laboratory for Electro-Optics Systems (LEOS / ISRO)',
        'agency_id' => 2,
        'specifications' => 'Wavelength range: 200–800 nm | Confirmed presence of Sulfur (S) on lunar south pole',
        'equip_id' => 15
    ],
    [
        'name' => 'APXS (Pragyan)',
        'official_name' => 'Alpha Particle X-Ray Spectrometer',
        'type' => 'X-Ray Fluorescence Contact Spectrometer',
        'purpose' => 'Determine elemental composition of lunar soil (Al, Si, K, Ca, Ti, Fe) using Curium-244 radioactive source.',
        'description' => 'Deployed close to the regolith to excite atoms via alpha radiation, measuring emitted characteristic X-rays.',
        'manufacturer' => 'Physical Research Laboratory (PRL), Ahmedabad / ISRO',
        'agency_id' => 2,
        'specifications' => 'Source: 244Cm | Resolution: ~150 eV at 5.9 keV | Detection: Na through Fe',
        'equip_id' => 15
    ],

    // --- HAYABUSA2 (Equipment #16) ---
    [
        'name' => 'ONC-T / ONC-W',
        'official_name' => 'Optical Navigation Cameras (Telescopic & Wide)',
        'type' => 'Multispectral Science & Navigation Cameras',
        'purpose' => 'Map asteroid Ryugu in high resolution, guide precision touchdowns, and analyze spectral absorption bands.',
        'description' => 'ONC-T features 7 bandpass filters from 390 nm to 550 nm; ONC-W provides wide-angle context during proximity operations.',
        'manufacturer' => 'JAXA / University of Tokyo',
        'agency_id' => 4,
        'specifications' => 'Resolution: 1024x1024 CCD | Filters: ul, b, v, Na, w, x, p | Ground resolution: <1 mm/pixel at touchdown',
        'equip_id' => 16
    ],
    [
        'name' => 'NIRS3',
        'official_name' => 'Near-Infrared Spectrometer',
        'type' => 'Infrared Reflectance Spectrometer',
        'purpose' => 'Detect hydrated minerals, organic matter, and water ice on Ryugu across the 1.8 to 3.2 micrometer absorption band.',
        'description' => 'Cooled InGaAs array sensor measuring asteroid surface reflectance to map hydroxyl (O-H) mineral distribution.',
        'manufacturer' => 'University of Aizu / JAXA',
        'agency_id' => 4,
        'specifications' => 'Spectral range: 1.8 to 3.2 µm | Spectral resolution: 18 nm | Field of view: 0.1°',
        'equip_id' => 16
    ],
    [
        'name' => 'SCI',
        'official_name' => 'Small Carry-on Impactor',
        'type' => 'Explosive Kinetic Projectile Excavatator',
        'purpose' => 'Create an artificial impact crater on asteroid Ryugu to expose and sample unweathered subsurface material.',
        'description' => 'Free-flying explosive kinetic weapon that detonate a 4.5 kg octogen shaped charge, accelerating a 2.5 kg pure copper liner into Ryugu at 2 km/s.',
        'manufacturer' => 'JAXA / IHI Aerospace',
        'agency_id' => 4,
        'specifications' => 'Projectile: 2.5 kg copper plate | Explosive: 4.5 kg Octogen | Impact velocity: 2.0 km/s | Created 10m crater',
        'equip_id' => 16
    ],

    // --- ROSETTA (Equipment #17) ---
    [
        'name' => 'OSIRIS',
        'official_name' => 'Optical, Spectroscopic, and Infrared Remote Imaging System',
        'type' => 'High-Resolution Science Imaging System',
        'purpose' => 'Map the nucleus of Comet 67P, identify active gas/dust jet vents, and locate the Philae lander on the surface.',
        'description' => 'Consists of a Narrow Angle Camera (NAC) for high-resolution nucleus topography and a Wide Angle Camera (WAC) for wide-field coma monitoring.',
        'manufacturer' => 'Max Planck Institute for Solar System Research',
        'agency_id' => 3,
        'specifications' => 'Resolution: 2048x2048 CCDs | 12 filters per camera | Resolving power: centimeters per pixel from orbit',
        'equip_id' => 17
    ],
    [
        'name' => 'ROSINA',
        'official_name' => 'Rosetta Orbiter Spectrometer for Ion and Neutral Analysis',
        'type' => 'Cometary Gas & Isotope Mass Spectrometer Suite',
        'purpose' => 'Determine composition and isotopic ratios of volatiles outgassing from Comet 67P\'s nucleus.',
        'description' => 'Double Focusing Mass Spectrometer (DFMS), Reflectron-type Time-of-Flight (RTOF) mass spectrometer, and Comet Pressure Sensor (COPS).',
        'manufacturer' => 'University of Bern (Switzerland)',
        'agency_id' => 3,
        'specifications' => 'Mass resolution: m/delta-m > 3000 | Discovered D/H ratio 3x Earth oceans & molecular O2',
        'equip_id' => 17
    ],
    [
        'name' => 'VIRTIS',
        'official_name' => 'Visible and Infrared Thermal Imaging Spectrometer',
        'type' => 'Hyperspectral Surface & Coma Mapper',
        'purpose' => 'Map composition, temperature distribution, and organic compounds on the comet surface.',
        'description' => 'Combines a visible spectrometer, near-infrared spectrometer, and high-resolution infrared sub-assembly.',
        'manufacturer' => 'INAF-IAPS (Italy) / LESIA (France)',
        'agency_id' => 3,
        'specifications' => 'Spectral range: 0.25 to 5.0 µm | Temperature sensitivity: 50 K to 400 K',
        'equip_id' => 17
    ],

    // --- PHILAE (Equipment #18) ---
    [
        'name' => 'ÇIVA',
        'official_name' => 'Comet Infrared and Visible Analyser',
        'type' => 'Micro-Camera & Stereoscopic Imaging Array',
        'purpose' => 'Capture 360-degree panoramic stereo imagery of the cometary surface immediately surrounding the Philae landing site.',
        'description' => 'Six identical micro-cameras mounted around the lander perimeter and one stereoscopic camera pair providing microscopic texture views.',
        'manufacturer' => 'Institut d\'Astrophysique Spatiale (IAS / CNRS), France',
        'agency_id' => 3,
        'specifications' => 'Sensor count: 7 micro-cameras | Mass: 1.3 kg | Captured first ground imagery from comet surface',
        'equip_id' => 18
    ],
    [
        'name' => 'COSAC',
        'official_name' => 'Cometary Sampling and Composition Experiment',
        'type' => 'Gas Chromatograph & Time-of-Flight Mass Spectrometer',
        'purpose' => 'Identify complex organic and volatile molecules in the comet surface material and determine their chirality.',
        'description' => 'Gas chromatograph equipped with 8 capillary columns and a high-resolution time-of-flight mass spectrometer fed by the SD2 drill pyrolysis ovens.',
        'manufacturer' => 'Max Planck Institute for Solar System Research',
        'agency_id' => 3,
        'specifications' => 'Mass: 4.5 kg | Identified 16 organic compounds including 4 nitrogen-bearing molecules on comet',
        'equip_id' => 18
    ],
    [
        'name' => 'MUPUS',
        'official_name' => 'Multi-Purpose Sensors for Surface and Sub-Surface Science',
        'type' => 'Thermal Penetrator & Mechanical Strength Hammer',
        'purpose' => 'Measure mechanical strength, thermal conductivity, and temperature profile of the comet nucleus crust.',
        'description' => 'Includes a deployable motorized hammer and sensor penetrator with 16 RTD thermal sensors driven into the comet surface.',
        'manufacturer' => 'DLR Institute of Planetary Research (Germany)',
        'agency_id' => 3,
        'specifications' => 'Impact energy: adjustable up to 2 Joules | Proved comet subsurface was as hard as solid rock',
        'equip_id' => 18
    ],
    [
        'name' => 'ROMAP',
        'official_name' => 'Rosetta Lander Magnetometer and Plasma Monitor',
        'type' => 'Fluxgate Magnetometer & Plasma Sensor',
        'purpose' => 'Measure magnetic fields and plasma environment on the surface of Comet 67P.',
        'description' => 'Triaxial fluxgate magnetometer mounted on a 60 cm sensor boom; proved that Comet 67P has no measurable global magnetic field.',
        'manufacturer' => 'TU Braunschweig (Germany)',
        'agency_id' => 3,
        'specifications' => 'Magnetic range: +/- 2,000 nT | Resolution: 12 pT | Confirmed unmagnetized comet nucleus',
        'equip_id' => 18
    ]
];

$insStmt = $pdo->prepare("
    INSERT INTO instruments (name, official_name, type, purpose, description, manufacturer, agency_id, specifications)
    VALUES (:name, :official_name, :type, :purpose, :description, :manufacturer, :agency_id, :specifications)
");
$relStmt = $pdo->prepare("
    INSERT IGNORE INTO equipment_instruments (equipment_id, instrument_id)
    VALUES (:equipment_id, :instrument_id)
");

foreach ($instruments as $inst) {
    // Check if instrument already exists by name
    $check = $pdo->prepare("SELECT id FROM instruments WHERE name = :name LIMIT 1");
    $check->execute(['name' => $inst['name']]);
    $existing = $check->fetch();

    if ($existing) {
        $instId = $existing['id'];
        $upInst = $pdo->prepare("
            UPDATE instruments SET
                official_name = :official_name, type = :type, purpose = :purpose,
                description = :description, manufacturer = :manufacturer, agency_id = :agency_id,
                specifications = :specifications
            WHERE id = :id
        ");
        $upInst->execute([
            'official_name' => $inst['official_name'],
            'type' => $inst['type'],
            'purpose' => $inst['purpose'],
            'description' => $inst['description'],
            'manufacturer' => $inst['manufacturer'],
            'agency_id' => $inst['agency_id'],
            'specifications' => $inst['specifications'],
            'id' => $instId
        ]);
    } else {
        $insStmt->execute([
            'name' => $inst['name'],
            'official_name' => $inst['official_name'],
            'type' => $inst['type'],
            'purpose' => $inst['purpose'],
            'description' => $inst['description'],
            'manufacturer' => $inst['manufacturer'],
            'agency_id' => $inst['agency_id'],
            'specifications' => $inst['specifications']
        ]);
        $instId = $pdo->lastInsertId();
    }

    $relStmt->execute([
        'equipment_id' => $inst['equip_id'],
        'instrument_id' => $instId
    ]);

    // Also link Pancam, Mini-TES, Mössbauer, RAT to Spirit (id: 6)
    if (in_array($inst['name'], ['Pancam', 'Mini-TES', 'Mössbauer Spectrometer', 'RAT'])) {
        $relStmt->execute(['equipment_id' => 6, 'instrument_id' => $instId]);
    }

    echo "Normalized Instrument: {$inst['name']} -> Equipment #{$inst['equip_id']}\n";
}

// =========================================================================
// 3. VERIFIED TIMELINE EVENTS
// =========================================================================

$timelineEvents = [
    // CURIOSITY (Equipment #8, Mission #8)
    [8, 8, 2011, '2011-11-26', 'Curiosity Liftoff from Cape Canaveral', 'Launches aboard Atlas V 541 rocket from Space Launch Complex 41 on a 254-day cruise to Mars.', 'CRITICAL'],
    [8, 8, 2012, '2012-08-06', 'Historic Sky Crane Landing at Gale Crater', 'Touches down at Bradbury Landing inside Gale Crater using the revolutionary Sky Crane tether descent stage.', 'CRITICAL'],
    [8, 8, 2013, '2013-02-08', 'First Powder Drill Sample on Mars', 'Curiosity drills a 6.4 cm deep hole into the John Klein mudstone outcrop, retrieving grey powder from ancient lake sediments.', 'HIGH'],
    [8, 8, 2013, '2013-03-12', 'Ancient Lake Habitability Confirmed', 'NASA announces that Gale Crater possessed a persistent freshwater lake with neutral pH, carbon, nitrogen, oxygen, phosphorus, and sulfur.', 'CRITICAL'],
    [8, 8, 2014, '2014-09-11', 'Arrival at the Foothills of Mount Sharp', 'Arrives at the Pahrump Hills at the base of Aeolis Mons, beginning its multi-year climb through planetary strata.', 'HIGH'],
    [8, 8, 2018, '2018-06-07', 'Discovery of 3-Billion-Year-Old Organic Molecules', 'SAM instrument detects ancient diverse organic macromolecules preserved within Lacustrine mudstones.', 'CRITICAL'],
    [8, 8, 2019, '2019-06-21', 'Methane Plume Detection in Gale Crater', 'SAM Tunable Laser Spectrometer detects an unexpected spike of background methane at 21 parts per billion.', 'HIGH'],
    [8, 8, 2022, '2022-08-06', 'Curiosity Surpasses 10 Years on Mars', 'Marks a full decade of continuous mobile science on the Red Planet, surpassing 28 km driven.', 'HIGH'],
    [8, 8, 2024, '2024-04-10', 'Investigation of the Gediz Vallis Water Channel', 'Explores boulders and sediment ridges deposited by ancient catastrophic wet debris flows descending Mount Sharp.', 'HIGH'],
    [8, 8, 2026, '2026-02-15', 'Ascending Upper Sulfate Terrains', 'Reaches higher elevation sulfate layers documenting Mars drying transition with over 33 km total driving.', 'HIGH'],

    // OPPORTUNITY (Equipment #7, Mission #7)
    [7, 7, 2003, '2003-07-08', 'Opportunity Liftoff from Cape Canaveral', 'Launches aboard a Boeing Delta II 7925H rocket from SLC-17B on a direct trajectory to Mars.', 'CRITICAL'],
    [7, 7, 2004, '2004-01-25', 'Airbag Landing in Eagle Crater', 'Bounces inside a small impact crater in Meridiani Planum, scoring a "hole-in-one" landing on the Martian plain.', 'CRITICAL'],
    [7, 7, 2004, '2004-03-02', 'Definitive Proof of Past Standing Water', 'Pancam and Mössbauer identify hematite blueberries and jarosite minerals, proving past standing acidic water.', 'CRITICAL'],
    [7, 7, 2006, '2006-09-26', 'Arrival at the Rim of Victoria Crater', 'Reaches the 800-meter-wide Victoria Crater after an arduous 21-month traverse across windblown ripple fields.', 'HIGH'],
    [7, 7, 2011, '2011-08-09', 'Rendezvous with Endeavour Crater', 'Arrives at the rim of the 22-kilometer-wide Endeavour Crater, uncovering sulfate veins formed by neutral-pH water.', 'HIGH'],
    [7, 7, 2015, '2015-03-24', 'First Off-World Marathon Completed', 'Becomes the first vehicle to complete a full 42.195 km marathon distance on the surface of another celestial body.', 'CRITICAL'],
    [7, 7, 2018, '2018-06-10', 'Final Transmission in Perseverance Valley', 'Sends its final engineering telemetry as a global dust storm darkens the sky over Endeavour Crater.', 'CRITICAL'],
    [7, 7, 2019, '2019-02-13', 'Mission Formally Concluded', 'After sending over 1,000 recovery commands without reply, NASA declares Opportunity\'s historic 15-year mission complete.', 'HIGH'],

    // SPIRIT (Equipment #6, Mission #6)
    [6, 6, 2003, '2003-06-10', 'Spirit Liftoff from Cape Canaveral', 'Launches aboard a Delta II 7925 rocket from SLC-17A, beginning the Mars Exploration Rover mission.', 'CRITICAL'],
    [6, 6, 2004, '2004-01-04', 'Touchdown in Gusev Crater', 'Airbag-cushioned landing in the basalt plains of Gusev Crater, beginning its robotic exploration.', 'CRITICAL'],
    [6, 6, 2005, '2005-08-21', 'Summits Husband Hill in Columbia Hills', 'Completes the first ascent of a planetary mountain by a rover, surveying the 107-meter summit of Husband Hill.', 'HIGH'],
    [6, 6, 2006, '2006-03-13', 'Right Front Wheel Motor Seizure', 'Right-front wheel jams; mission engineers adapt by commanding rover to drive backward dragging dead wheel.', 'HIGH'],
    [6, 6, 2007, '2007-05-03', 'Discovery of 90% Pure Silica Hot Spring Deposits', 'The dragged dead wheel carves a trench exposing bright white opaline silica, proving ancient volcanic hot springs.', 'CRITICAL'],
    [6, 6, 2009, '2009-05-01', 'Entrapment in Soft Sulfate Sands at Troy', 'Breaks through a thin surface crust and becomes trapped in loose sulfate sands on the west flank of Home Plate.', 'CRITICAL'],
    [6, 6, 2010, '2010-03-22', 'Final Communication Received', 'Transmits final signal before Martian winter sets in, concluding 2,210 sols of operations (24x planned warranty).', 'HIGH'],

    // APOLLO 15 LRV (Equipment #2, Mission #2)
    [2, 2, 1971, '1971-07-26', 'Apollo 15 Saturn V Liftoff', 'Saturn V rocket SA-510 blasts off from Launch Complex 39A at Kennedy Space Center carrying the first Lunar Roving Vehicle.', 'CRITICAL'],
    [2, 2, 1971, '1971-07-30', 'Touchdown at Hadley-Apennine', 'Lunar Module Falcon touches down in the valley between Hadley Rille and the towering Apennine Mountains.', 'CRITICAL'],
    [2, 2, 1971, '1971-07-31', 'Unfolding and First Drive on the Moon', 'Dave Scott and Jim Irwin deploy LRV-001 from Falcon quadrant; Scott drives the first human miles on the lunar surface (EVA 1).', 'CRITICAL'],
    [2, 2, 1971, '1971-08-01', 'Discovery of the Genesis Rock (EVA 2)', 'Astronauts drive rover up the slope of Mt. Hadley Delta, discovering Sample 15415, the 4.1-billion-year-old anorthosite.', 'CRITICAL'],
    [2, 2, 1971, '1971-08-02', 'Traverse to Hadley Rille & Final Parking', 'Rover completes traverse to the rim of the 300m-deep Hadley Rille; parked at VIP site 90m east of LM Falcon.', 'HIGH'],
    [2, 2, 1971, '1971-08-02', 'Broadcasts LM Falcon Lunar Blastoff', 'The ground-controlled RCA color camera on LRV-001 tracks and broadcasts live color television of the Falcon ascent stage liftoff.', 'CRITICAL'],

    // VOYAGER 1 (Equipment #3, Mission #3)
    [3, 3, 1977, '1977-09-05', 'Voyager 1 Liftoff from Cape Canaveral', 'Titan IIIE-Centaur launches Voyager 1 on a fast trajectory to Jupiter and Saturn.', 'CRITICAL'],
    [3, 3, 1979, '1979-03-05', 'Jupiter Encounter & Discovery of Volcanism on Io', 'Closest approach to Jupiter (349,000 km); reveals active volcanic plumes on Io and Jovian ring system.', 'CRITICAL'],
    [3, 3, 1980, '1980-11-12', 'Saturn Encounter & Titan Flyby', 'Flies past Saturn (124,000 km) and Titan (6,490 km), confirming Titan\'s thick nitrogen-rich atmosphere.', 'CRITICAL'],
    [3, 3, 1990, '1990-02-14', 'The Pale Blue Dot Solar System Portrait', 'Turns cameras back toward the solar system from 6 billion km to capture the legendary family portrait including Earth.', 'CRITICAL'],
    [3, 3, 2004, '2004-12-16', 'Crossing the Termination Shock', 'Crosses into the turbulent Heliosheath at 94 AU where solar wind abruptly slows below speed of sound.', 'HIGH'],
    [3, 3, 2012, '2012-08-25', 'First Human-Made Object in Interstellar Space', 'Crosses the Heliopause at 121.6 AU, recording a drop in solar particles and a 40x jump in galactic cosmic rays.', 'CRITICAL'],
    [3, 3, 2024, '2024-04-20', 'JPL Restores Deep Space Data Stream', 'NASA engineers successfully patch the Flight Data Subsystem memory code across 24 billion km.', 'HIGH'],
    [3, 3, 2026, '2026-03-20', 'Interstellar Flight at >163 AU', 'Continues transmitting real-time plasma and cosmic ray measurements from interstellar space.', 'HIGH'],

    // CHANDRAYAAN-3 VIKRAM & PRAGYAN (Equipment #14 & #15, Mission #14)
    [14, 14, 2023, '2023-07-14', 'Chandrayaan-3 LVM3-M4 Liftoff', 'ISRO launches Chandrayaan-3 from Satish Dhawan Space Centre in Sriharikota into Earth parking orbit.', 'CRITICAL'],
    [14, 14, 2023, '2023-08-05', 'Lunar Orbit Insertion (LOI)', 'Spacecraft successfully fires propulsion engine to enter orbit around the Moon.', 'HIGH'],
    [14, 14, 2023, '2023-08-17', 'Vikram Lander Module Separation', 'Lander Module separates from Propulsion Module, beginning powered descent preparation.', 'HIGH'],
    [14, 14, 2023, '2023-08-23', 'Historic Touchdown at Shiv Shakti Point', 'Vikram executes flawless soft landing at 69.37° S near the lunar south pole, making India the 4th nation to soft-land.', 'CRITICAL'],
    [15, 14, 2023, '2023-08-24', 'Pragyan Rover Rollout on Lunar Regolith', 'Pragyan rover rolls down Vikram\'s ramp, taking the first wheel-tracks at the lunar south pole.', 'CRITICAL'],
    [14, 14, 2023, '2023-08-27', 'ChaSTE Measures Steep Regolith Thermal Gradient', 'Thermal probe reveals sharp 60°C temperature drop between surface (+50°C) and 8 cm depth (-10°C).', 'HIGH'],
    [15, 14, 2023, '2023-08-29', 'LIBS Confirms Sulfur on Lunar South Pole', 'Pragyan\'s laser spectrometer unambiguously confirms presence of Sulfur (S) in south polar regolith.', 'CRITICAL'],
    [14, 14, 2023, '2023-09-03', 'Vikram Executes Historic Engine Hop Test', 'Fires engines to lift 40 cm, drift horizontally, and land safely, proving re-launch capability.', 'CRITICAL'],
    [15, 14, 2023, '2023-09-02', 'Pragyan Put to Sleep After 101.4m Drive', 'Completes 101.4 meter traverse; parked safely in sunlight as lunar night approaches.', 'HIGH'],

    // HAYABUSA2 (Equipment #16, Mission #15)
    [16, 15, 2014, '2014-12-03', 'Hayabusa2 Liftoff from Tanegashima', 'Launches aboard H-IIA 202 rocket on a 3.5-year ion-powered cruise to asteroid Ryugu.', 'CRITICAL'],
    [16, 15, 2018, '2018-06-27', 'Arrival at Asteroid 162173 Ryugu', 'Arrives at hovering altitude of 20 km above the carbonaceous C-type near-Earth asteroid.', 'CRITICAL'],
    [16, 15, 2018, '2018-09-21', 'MINERVA-II1 Surface Rover Deployment', 'Deploys rovers Rover-1A and 1B, capturing the first hopping surface images on an asteroid.', 'HIGH'],
    [16, 15, 2019, '2019-02-21', 'First Surface Touchdown on Ryugu', 'Descends to surface, fires 5g tantalum bullet into regolith, and captures asteroid dust into sample horn.', 'CRITICAL'],
    [16, 15, 2019, '2019-04-05', 'SCI Kinetic Impact Bombardment', 'Fires 2.5 kg copper projectile into Ryugu at 2 km/s, creating an artificial 10-meter impact crater.', 'CRITICAL'],
    [16, 15, 2019, '2019-07-11', 'Second Touchdown in Impact Crater', 'Lands inside the fresh impact zone to collect pristine subsurface asteroid material shielded from solar radiation.', 'CRITICAL'],
    [16, 15, 2020, '2020-12-06', 'Sample Capsule Return to Earth', 'Releases sample return capsule into Australian desert at Woomera, delivering 5.4g of pristine carbonaceous matter.', 'CRITICAL'],
    [16, 15, 2026, '2026-03-20', 'Hayabusa2# Extended Mission Active', 'Cruising in extended mission toward micro-asteroid 1998 KY26 for rendezvous in July 2031.', 'HIGH'],

    // ROSETTA & PHILAE (Equipment #17 & #18, Mission #16)
    [17, 16, 2004, '2004-03-02', 'Rosetta & Philae Ariane 5 Liftoff', 'Launches from Kourou, French Guiana, embarking on a 10-year, 6.4-billion-km gravity-assist journey.', 'CRITICAL'],
    [17, 16, 2014, '2014-01-20', 'Rosetta Wake-Up from Deep-Space Hibernation', 'Wakes up after 31 months of radio silence as solar panels receive sufficient light near Jupiter\'s orbit.', 'HIGH'],
    [17, 16, 2014, '2014-08-06', 'First Orbit Around a Comet Nucleus', 'Rendezvous with Comet 67P/Churyumov-Gerasimenko, becoming the first spacecraft to enter orbit around a comet.', 'CRITICAL'],
    [18, 16, 2014, '2014-11-12', 'Philae Historic Landing on Comet 67P', 'Philae lander separates from Rosetta, touches down at Agilkia, bounces twice, and settles in Abydos crevice.', 'CRITICAL'],
    [18, 16, 2014, '2014-11-14', 'Philae Completes Primary Science Sequence', 'COSAC detects 16 organic compounds including 4 nitrogen molecules; MUPUS confirms rock-hard ice crust.', 'CRITICAL'],
    [17, 16, 2014, '2014-12-10', 'ROSINA Measures Heavy Water Ratio', 'Reveals Comet 67P water has 3x Earth D/H ratio, showing Earth water did not originate from this comet family.', 'CRITICAL'],
    [17, 16, 2015, '2015-08-13', 'Comet Perihelion Accompaniement', 'Rosetta escorts Comet 67P through its closest approach to the Sun, documenting massive gas and dust eruptions.', 'HIGH'],
    [17, 16, 2016, '2016-09-02', 'Rosetta OSIRIS Locates Philae in Abydos', 'High-resolution camera spots Philae wedged under a boulder, solving the mystery of its exact location.', 'HIGH'],
    [17, 16, 2016, '2016-09-30', 'Rosetta Controlled Descent onto Comet 67P', 'Rosetta touches down in the Ma\'at region, sending close-up images down to 20 meters before shutdown.', 'CRITICAL']
];

$tStmt = $pdo->prepare("
    INSERT INTO timeline_events (equipment_id, mission_id, year, event_date, title, description, importance)
    VALUES (:equipment_id, :mission_id, :year, :event_date, :title, :description, :importance)
");

foreach ($timelineEvents as $te) {
    // Check if event already exists
    $chk = $pdo->prepare("SELECT id FROM timeline_events WHERE equipment_id = :eq AND title = :title LIMIT 1");
    $chk->execute(['eq' => $te[0], 'title' => $te[4]]);
    if (!$chk->fetch()) {
        $tStmt->execute([
            'equipment_id' => $te[0],
            'mission_id' => $te[1],
            'year' => $te[2],
            'event_date' => $te[3],
            'title' => $te[4],
            'description' => $te[5],
            'importance' => $te[6]
        ]);
        echo "Timeline: {$te[4]} (Equip #{$te[0]})\n";
    }
}

// =========================================================================
// 4. OFFICIAL IMAGES (NASA, ESA, ISRO, JAXA)
// =========================================================================

$imagesData = [
    // CURIOSITY (Equipment #8)
    [8, 8, 'https://images-assets.nasa.gov/image/PIA19808/PIA19808~orig.jpg', 'Curiosity Rover Self-Portrait at Namib Dune', 'Curiosity self-portrait combined from multiple MAHLI exposures at Namib Dune on Mount Sharp.', 'NASA / JPL-Caltech / MSSS', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'hero'],
    [8, 8, 'https://images-assets.nasa.gov/image/PIA13568/PIA13568~orig.jpg', 'Mars Science Laboratory Atlas V Liftoff', 'Atlas V 541 lifts off from SLC-41 carrying Curiosity to Mars on 26 November 2011.', 'NASA / Bill Ingalls', 'Public Domain', 'NASA Image and Video Library', 'NASA', 'launch'],
    [8, 8, 'https://images-assets.nasa.gov/image/PIA16225/PIA16225~orig.jpg', 'First Drill Hole at John Klein Outcrop', 'Mastcam image showing the first percussive drill hole drilled by Curiosity into ancient Martian mudstone.', 'NASA / JPL-Caltech / MSSS', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'surface'],
    [8, 8, 'https://images-assets.nasa.gov/image/PIA25686/PIA25686~orig.jpg', 'Curiosity at Gediz Vallis Ridge', 'Curiosity Navcam panorama showing rugged debris ridges in Gediz Vallis channel on Mount Sharp.', 'NASA / JPL-Caltech', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'operation'],

    // OPPORTUNITY (Equipment #7)
    [7, 7, 'https://images-assets.nasa.gov/image/PIA18076/PIA18076~orig.jpg', 'Opportunity Rover Self-Portrait at Endeavour Rim', 'Opportunity Pancam mosaic self-portrait celebrating 10 years of exploration on Mars.', 'NASA / JPL-Caltech / Cornell', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'hero'],
    [7, 7, 'https://images-assets.nasa.gov/image/PIA04413/PIA04413~orig.jpg', 'Delta II Liftoff Carrying Opportunity Rover', 'Boeing Delta II 7925H rocket launches MER-B Opportunity from Cape Canaveral.', 'NASA / JPL', 'Public Domain', 'NASA Image and Video Library', 'NASA', 'launch'],
    [7, 7, 'https://images-assets.nasa.gov/image/PIA05548/PIA05548~orig.jpg', 'Martian Hematite Blueberries in Eagle Crater', 'Microscopic Imager close-up of iron-rich spherules proving ancient standing liquid water.', 'NASA / JPL-Caltech / USGS', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'surface'],
    [7, 7, 'https://images-assets.nasa.gov/image/PIA13783/PIA13783~orig.jpg', 'Victoria Crater Panorama from Duck Bay', 'Pancam panoramic view of dramatic scalloped cliffs inside Victoria Crater.', 'NASA / JPL-Caltech / Cornell', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'operation'],

    // SPIRIT (Equipment #6)
    [6, 6, 'https://images-assets.nasa.gov/image/PIA07942/PIA07942~orig.jpg', 'Spirit Rover Panorama from Husband Hill Summit', 'Spirit Pancam 360-degree panorama captured from the summit of Husband Hill overlooking Gusev Crater.', 'NASA / JPL-Caltech / Cornell', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'hero'],
    [6, 6, 'https://images-assets.nasa.gov/image/PIA04411/PIA04411~orig.jpg', 'Spirit Liftoff Aboard Delta II', 'Delta II rocket lifts off from Cape Canaveral SLC-17A carrying the Spirit rover to Mars.', 'NASA / JPL', 'Public Domain', 'NASA Image and Video Library', 'NASA', 'launch'],
    [6, 6, 'https://images-assets.nasa.gov/image/PIA09400/PIA09400~orig.jpg', 'Pure Opaline Silica Soil at Home Plate', 'Pancam false-color view of brilliant white 90% pure silica soil churned up by Spirit\'s jammed right wheel.', 'NASA / JPL-Caltech / Cornell', 'Public Domain', 'NASA Image and Video Library', 'NASA JPL', 'surface'],

    // APOLLO 15 LRV (Equipment #2)
    [2, 2, 'https://images-assets.nasa.gov/image/as15-88-11866/as15-88-11866~orig.jpg', 'Apollo 15 Lunar Roving Vehicle at Hadley-Apennine', 'Jim Irwin stands next to LRV-001 with Mt. Hadley in the background during EVA 1.', 'NASA / Dave Scott', 'Public Domain', 'NASA Human Space Flight Gallery', 'NASA', 'hero'],
    [2, 2, 'https://images-assets.nasa.gov/image/S71-39962/S71-39962~orig.jpg', 'Saturn V Liftoff Carrying Apollo 15', 'Saturn V SA-510 rocket blasts off from LC-39A carrying Apollo 15 and the first Lunar Roving Vehicle.', 'NASA', 'Public Domain', 'NASA Johnson Space Center', 'NASA', 'launch'],
    [2, 2, 'https://images-assets.nasa.gov/image/as15-85-11471/as15-85-11471~orig.jpg', 'LRV Parked on the Rim of Hadley Rille', 'LRV-001 parked near the edge of the 300-meter deep gorge of Hadley Rille.', 'NASA / Dave Scott', 'Public Domain', 'NASA Human Space Flight Gallery', 'NASA', 'surface'],

    // VOYAGER 1 (Equipment #3)
    [3, 3, 'https://images-assets.nasa.gov/image/PIA17046/PIA17046~orig.jpg', 'Voyager 1 Spacecraft in the Interstellar Medium', 'Artist rendering of Voyager 1 in the interstellar medium with our Sun receding as a distant star.', 'NASA / JPL-Caltech', 'Public Domain', 'NASA JPL', 'NASA JPL', 'hero'],
    [3, 3, 'https://images-assets.nasa.gov/image/PIA01543/PIA01543~orig.jpg', 'Titan IIIE Liftoff with Voyager 1', 'Titan IIIE-Centaur launches Voyager 1 from SLC-41 at Cape Canaveral on 05 September 1977.', 'NASA / Kennedy Space Center', 'Public Domain', 'NASA Image and Video Library', 'NASA', 'launch'],
    [3, 3, 'https://images-assets.nasa.gov/image/PIA00452/PIA00452~orig.jpg', 'The Pale Blue Dot', 'Voyager 1 view of Earth as a tiny speck of light in a sunbeam from 6 billion kilometers away.', 'NASA / JPL-Caltech', 'Public Domain', 'NASA JPL', 'NASA JPL', 'surface'],

    // CHANDRAYAAN-3 VIKRAM & PRAGYAN (Equipment #14 & #15)
    [14, 14, 'https://images-assets.nasa.gov/image/GSFC_20230906_LRO_m1449830843R/GSFC_20230906_LRO_m1449830843R~orig.jpg', 'Chandrayaan-3 Vikram Lander at Shiv Shakti Point', 'Lunar Reconnaissance Orbiter high-resolution camera view of Vikram Lander on the lunar south pole.', 'NASA / GSFC / Arizona State University / ISRO', 'Public Domain', 'NASA LRO', 'ISRO / NASA', 'hero'],
    [15, 14, 'https://images-assets.nasa.gov/image/PIA26090/PIA26090~orig.jpg', 'Pragyan Rover on Lunar South Polar Regolith', 'Pragyan rover imaged on the high-latitude lunar surface following ramp egress from Vikram.', 'ISRO', 'Fair Use / Educational Documentation', 'ISRO Chandrayaan-3 Gallery', 'ISRO', 'hero'],

    // HAYABUSA2 (Equipment #16)
    [16, 15, 'https://images-assets.nasa.gov/image/PIA22485/PIA22485~orig.jpg', 'Asteroid 162173 Ryugu Imaged by Hayabusa2', 'High-resolution ONC-T composite of rubble-pile asteroid Ryugu captured from 20 km distance.', 'JAXA / University of Tokyo / Kochi University / Rikkyo / Nagoya', 'Fair Use / Scientific Documentation', 'JAXA Press Portal', 'JAXA', 'hero'],

    // ROSETTA (Equipment #17) & PHILAE (Equipment #18)
    [17, 16, 'https://images-assets.nasa.gov/image/PIA18899/PIA18899~orig.jpg', 'Comet 67P Nucleus Imaged by Rosetta OSIRIS', 'Comet 67P/Churyumov-Gerasimenko showing dramatic cliff topography and gas jet activity.', 'ESA / Rosetta / MPS for OSIRIS Team', 'CC BY-SA 4.0 IGO', 'ESA Space in Images', 'ESA', 'hero'],
    [18, 16, 'https://images-assets.nasa.gov/image/PIA19041/PIA19041~orig.jpg', 'Philae Lander on Comet Surface in Abydos', 'OSIRIS narrow-angle camera view showing the Philae lander wedged under a rocky overhang in Abydos.', 'ESA / Rosetta / MPS for OSIRIS Team', 'CC BY-SA 4.0 IGO', 'ESA Space in Images', 'ESA', 'hero']
];

$imgStmt = $pdo->prepare("
    INSERT INTO images (equipment_id, mission_id, image_url, title, description, credit, license, source, source_organization, image_type)
    VALUES (:equipment_id, :mission_id, :image_url, :title, :description, :credit, :license, :source, :source_organization, :image_type)
");

foreach ($imagesData as $im) {
    // Check if image already exists
    $chk = $pdo->prepare("SELECT id FROM images WHERE equipment_id = :eq AND image_type = :type AND title = :title LIMIT 1");
    $chk->execute(['eq' => $im[0], 'type' => $im[9], 'title' => $im[3]]);
    if (!$chk->fetch()) {
        $imgStmt->execute([
            'equipment_id' => $im[0],
            'mission_id' => $im[1],
            'image_url' => $im[2],
            'title' => $im[3],
            'description' => $im[4],
            'credit' => $im[5],
            'license' => $im[6],
            'source' => $im[7],
            'source_organization' => $im[8],
            'image_type' => $im[9]
        ]);
        echo "Image: {$im[3]} (Equip #{$im[0]})\n";
    }
}

// =========================================================================
// 5. AUTHORITATIVE RESEARCH SOURCES
// =========================================================================

$sourcesData = [
    // CURIOSITY
    [8, 8, 'NASA Mars Science Laboratory / Curiosity Portal', 'NASA Jet Propulsion Laboratory', 'https://mars.nasa.gov/msl/home/', 'Official Mission Registry', 'Official NASA mission home providing real-time telemetry, instrument overviews, and science publications.', '2026-03-01'],
    [8, 8, 'NASA Science: Curiosity Rover Fact Sheet & Science', 'NASA Science Mission Directorate', 'https://science.nasa.gov/mission/msl-curiosity/', 'Scientific Archive', 'Comprehensive technical overview of MSL science payload, sampling systems, and yellowknife bay findings.', '2026-03-01'],

    // OPPORTUNITY
    [7, 7, 'NASA JPL Mars Exploration Rovers Archive', 'NASA Jet Propulsion Laboratory', 'https://mars.nasa.gov/mer/', 'Mission Archive', 'Complete historical repository of Opportunity (MER-B) traverse maps, sol logs, and discoveries.', '2026-03-01'],
    [7, 7, 'NASA Press: NASA\'s Record-Setting Opportunity Rover Mission Ends', 'NASA Headquarters', 'https://www.nasa.gov/press-release/nasas-record-setting-opportunity-rover-mission-on-mars-comes-to-end', 'Official Press Release', 'Formal mission completion announcement documenting 5,111 sols and 45.16 km driven.', '2019-02-13'],

    // SPIRIT
    [6, 6, 'NASA JPL Spirit Rover Mission Summary', 'NASA Jet Propulsion Laboratory', 'https://mars.nasa.gov/mer/mission/overview/spirit/', 'Official Mission Registry', 'Operational archive documenting Spirit\'s exploration of Gusev Crater and Husband Hill.', '2026-03-01'],

    // APOLLO 15 LRV
    [2, 2, 'NASA Apollo 15 Lunar Surface Journal', 'NASA History Division', 'https://www.nasa.gov/history/alsj/a15/a15.html', 'Primary Historical Record', 'Annotated transcript and technical logs of all three LRV traverses and lunar rover systems.', '2026-03-01'],
    [2, 2, 'Boeing / NASA Lunar Roving Vehicle Operations Handbook', 'NASA Marshall Space Flight Center', 'https://www.nasa.gov/history/alsj/LRV_Ops_Manual.pdf', 'Engineering Manual', 'Complete engineering specifications, battery schematics, and harmonic drive parameters for LRV-001.', '1971-04-19'],

    // VOYAGER 1
    [3, 3, 'NASA JPL Voyager Interstellar Mission Real-Time Portal', 'NASA Jet Propulsion Laboratory', 'https://voyager.jpl.nasa.gov/', 'Official Mission Registry', 'Real-time distance counters, instrument health monitors, and interstellar plasma telemetry.', '2026-03-01'],
    [3, 3, 'NASA Press: Voyager 1 Becomes First Spacecraft to Enter Interstellar Space', 'NASA Headquarters / Science', 'https://www.nasa.gov/mission_pages/voyager/voyager20130912.html', 'Peer-Reviewed Announcement', 'Science paper announcement confirming Heliopause crossing at 121.6 AU.', '2013-09-12'],

    // CHANDRAYAAN-3
    [14, 14, 'ISRO Official Portal: Chandrayaan-3 Mission', 'Indian Space Research Organisation (ISRO)', 'https://www.isro.gov.in/Chandrayaan3.html', 'Official Government Agency', 'Technical dossiers, payload specifications, and landing telemetry for Vikram and Pragyan.', '2026-03-01'],
    [15, 14, 'ISRO Press: In-situ Scientific Experiments on Lunar South Pole', 'ISRO Media Centre', 'https://www.isro.gov.in/Chandrayaan3_Details.html', 'Scientific Press Release', 'First in-situ detection of Sulfur (S) on lunar south pole by LIBS instrument.', '2023-08-30'],

    // HAYABUSA2
    [16, 15, 'JAXA Hayabusa2 Project Official Portal', 'Japan Aerospace Exploration Agency (JAXA)', 'https://www.hayabusa2.jaxa.jp/en/', 'Official Space Agency Archive', 'Detailed mission reports covering Ryugu proximity operations, kinetic impact, and sample recovery.', '2026-03-01'],

    // ROSETTA & PHILAE
    [17, 16, 'ESA Rosetta Mission Portal', 'European Space Agency (ESA)', 'https://www.esa.int/Enabling_Support/Operations/Rosetta', 'Official Space Agency Archive', 'Comprehensive archive of Rosetta orbiter telemetry, comet perihelion monitoring, and controlled landing.', '2026-03-01'],
    [18, 16, 'ESA Philae Lander Science Archive', 'European Space Agency (ESA)', 'https://www.esa.int/Science_Exploration/Space_Science/Rosetta/Philae', 'Science Archive', 'Primary scientific documentation of the first soft touchdown on a comet nucleus and COSAC discoveries.', '2026-03-01']
];

$sStmt = $pdo->prepare("
    INSERT INTO sources (equipment_id, mission_id, source_name, organization, source_url, source_type, description, verified_date, accessed_at)
    VALUES (:equipment_id, :mission_id, :source_name, :organization, :source_url, :source_type, :description, :verified_date, CURDATE())
");

foreach ($sourcesData as $sd) {
    $chk = $pdo->prepare("SELECT id FROM sources WHERE equipment_id = :eq AND source_name = :name LIMIT 1");
    $chk->execute(['eq' => $sd[0], 'name' => $sd[2]]);
    if (!$chk->fetch()) {
        $sStmt->execute([
            'equipment_id' => $sd[0],
            'mission_id' => $sd[1],
            'source_name' => $sd[2],
            'organization' => $sd[3],
            'source_url' => $sd[4],
            'source_type' => $sd[5],
            'description' => $sd[6],
            'verified_date' => $sd[7]
        ]);
        echo "Source: {$sd[2]} (Equip #{$sd[0]})\n";
    }
}

echo "\nPhase 4 Flagship Seeding Completed Successfully!\n";
