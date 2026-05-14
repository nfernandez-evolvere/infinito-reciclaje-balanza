/* global React */
const { createContext, useContext, useState, useEffect, useMemo } = React;

const AppContext = createContext(null);

function AppProvider({ children }) {
  const [zonas,         setZonas]         = useState([]);
  const [zonaServicios, setZonaServicios] = useState([]);
  const [vehiculos,     setVehiculos]     = useState([]);
  const [tiposVehiculo, setTiposVehiculo] = useState([]);
  const [servicios,     setServicios]     = useState([]);
  const [usuarios,      setUsuarios]      = useState([]);
  const [pesajes,       setPesajes]       = useState([]);
  const [pesajesLog,    setPesajesLog]    = useState([]);
  const [loading,       setLoading]       = useState(true);

  useEffect(() => {
    Promise.all([
      fetch("./data/zonas.json").then((r) => r.json()),
      fetch("./data/zona_servicios.json").then((r) => r.json()),
      fetch("./data/vehiculos.json").then((r) => r.json()),
      fetch("./data/tipos_vehiculo.json").then((r) => r.json()),
      fetch("./data/servicios.json").then((r) => r.json()),
      fetch("./data/usuarios.json").then((r) => r.json()),
      fetch("./data/pesajes.json").then((r) => r.json()),
      fetch("./data/pesajes_log.json").then((r) => r.json()),
    ]).then(([z, zs, v, tv, s, u, p, pl]) => {
      setZonas(z);
      setZonaServicios(zs);
      setVehiculos(v);
      setTiposVehiculo(tv);
      setServicios(s);
      setUsuarios(u);
      setPesajes(p);
      setPesajesLog(pl);
      setLoading(false);
    }).catch((err) => {
      console.error("Error cargando datos:", err);
      setLoading(false);
    });
  }, []);

  // Valores derivados que usan múltiples componentes
  const servicioNames  = useMemo(() => servicios.map((s) => s.nombre), [servicios]);
  const zonaNames      = useMemo(() => zonas.filter((z) => z.estado === "Activo").map((z) => z.nombre), [zonas]);
  const vehicleTypeMap = useMemo(() => Object.fromEntries(tiposVehiculo.map((t) => [t.nombre, t])), [tiposVehiculo]);
  const servicioCascade = useMemo(() => Object.fromEntries(servicios.map((s) => [s.nombre, { tipoSugerido: s.tipoSugerido }])), [servicios]);

  return (
    <AppContext.Provider value={{
      zonas,         setZonas,
      zonaServicios, setZonaServicios,
      vehiculos,     setVehiculos,
      tiposVehiculo, setTiposVehiculo,
      servicios,     setServicios,
      usuarios,      setUsuarios,
      pesajes,       setPesajes,
      pesajesLog,    setPesajesLog,
      servicioNames,
      zonaNames,
      vehicleTypeMap,
      servicioCascade,
      loading,
    }}>
      {children}
    </AppContext.Provider>
  );
}

function useAppContext() {
  return useContext(AppContext);
}

Object.assign(window, { AppContext, AppProvider, useAppContext });
