import React from 'react';
import './App.css';
import Header from './components/Header'; // Ajusta la ruta seg�n sea necesario
import Footer from './components/Footer'; // Ajusta la ruta seg�n sea necesario
import HomePage from './pages/HomePage'; // Ajusta la ruta seg�n sea necesario

function App() {
    return (
        <div className="App">
            <Header />
            <HomePage />
            <Footer />
        </div>
    );
}

export default App;
