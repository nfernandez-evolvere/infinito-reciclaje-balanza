{{-- Partial standalone de la tabla del historial. Se sirve por
     admin.reportes.historial.parcial y el front lo inyecta en #historial-tabla
     para refrescar estados en vivo. Reusa el mismo componente que la vista. --}}
<x-domain.reportes.tabla-historial :historial="$historial" />
