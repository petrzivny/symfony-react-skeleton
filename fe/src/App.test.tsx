import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { App } from './App';

vi.mock('axios', () => ({
  default: {
    get: vi.fn(() =>
      Promise.resolve({
        data: {
          data: {
            optionName: 'test-option',
            isSupported: 'YES',
            phpVersion: '8.3',
            symfonyVersion: '7.2',
          },
        },
      }),
    ),
  },
}));

describe('App', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders skeleton UI and backend-driven copy after queries settle', async () => {
    render(<App />);

    expect(screen.getByRole('link', { name: /vite logo/i })).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /react logo/i })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /count is 0/i })).toBeInTheDocument();

    expect(await screen.findByText(/Symfony 7\.2 on Php 8\.3 \(BE\)/)).toBeInTheDocument();
    expect(screen.getByText(/Data from Backend database received\./)).toBeInTheDocument();
    expect(screen.getByText(/test-option:/)).toBeInTheDocument();
    expect(screen.getByText(/Is supported/)).toBeInTheDocument();
  });

  it('increments the counter when the button is clicked', async () => {
    const user = userEvent.setup();
    render(<App />);

    await screen.findByText(/Symfony 7\.2 on Php 8\.3 \(BE\)/);

    await user.click(screen.getByRole('button', { name: /count is 0/i }));

    expect(screen.getByRole('button', { name: /count is 1/i })).toBeInTheDocument();
  });
});
