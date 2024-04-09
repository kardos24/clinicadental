import React from 'react';
import Inicio from '../components/Inicio';
import DondeEstamos from '../components/DondeEstamos';
import Servicios from '../components/Servicios';
import Contactos from '../components/Contactos';

function HomePage() {
    return (
        <main>
            <Inicio />
            <DondeEstamos />
            <Servicios />
            <Contactos />
        </main>
    );
}

export default HomePage;