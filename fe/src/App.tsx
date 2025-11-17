import React, { useState, version } from 'react';
import reactLogo from './assets/react.svg';
import symfonyLogo from './assets/symfony.svg';
import './App.css';
import BackendValue from './BackendValue.tsx';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import BackendVersions from './BackendVersions.tsx';

function App(): React.JSX.Element {
  const [count, setCount] = useState(0);

  const queryClient = new QueryClient();

  return (
    <>
      <div>
        <a href="https://symfony.com/doc/current/index.html" target="_blank" rel="noreferrer">
          <img src={symfonyLogo} alt="Vite logo" />
        </a>
        <a href="https://react.dev" target="_blank" rel="noreferrer">
          <img src={reactLogo} className="logo react" alt="React logo" />
        </a>
      </div>
      <QueryClientProvider client={queryClient}>
        <h2>
          <BackendVersions /> and React {version} with Vite (FE) skeleton demo
        </h2>
        <BackendValue />
      </QueryClientProvider>
      <div className="card">
        <button onClick={() => setCount((count) => count + 1)}>count is {count}</button>
        <p>
          Edit <code>src/BackendValue.tsx</code> and save to test HMR
        </p>
      </div>
    </>
  );
}

export default App;
