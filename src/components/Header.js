import React from 'react';
import { Link } from 'react-router-dom';
import logo from '../image/logo.png'; // Asegúrate de que la ruta al logo sea correcta

function Header() {
    return (
        <header className="header">
            <div className="logo-container">
                <img src={logo} alt="Logo" className="logo" />
                <h1>Centro Dental Mula</h1>
            </div>
            <nav className="navbar">
                <Link to="/">Inicio</Link>
                <Link to="/#donde-estamos">Dónde estamos</Link>
                <Link to="/#servicios">Servicios</Link>
                <Link to="/#contactos">Contactos</Link>
                <Link to="/clientes">Clientes</Link>
            </nav>
        </header>
    );
}

export default Header;