import React from 'react';
import type { PublicProject } from '../services/contentService';
import { useIntersectionObserver } from '../hooks/useIntersectionObserver';
import { ProjectsPresentation } from './ProjectsPresentation';

interface PortfolioProps {
  onSelectProject: (title: string, desc: string, tags: string[]) => void;
  projects?: PublicProject[];
}

export const Portfolio: React.FC<PortfolioProps> = ({ onSelectProject, projects }) => {
  const { ref, isVisible } = useIntersectionObserver({ threshold: 0.1 });

  return (
    <section id="portfolio" ref={ref} style={{ padding: '100px 0', position: 'relative' }}>
      <div className="triangle-decor triangle-cyan" style={{ top: '5%', right: '6%', opacity: 0.18 }} />
      <div className="container">
        <div
          className={`reveal-item ${isVisible ? 'reveal-visible' : ''}`}
          style={{ textAlign: 'center', maxWidth: '720px', margin: '0 auto 60px' }}
        >
          <div
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '8px',
              padding: '4px 14px',
              borderRadius: '20px',
              background: 'rgba(35, 136, 255, 0.1)',
              border: '1px solid rgba(35, 136, 255, 0.3)',
              fontSize: '12px',
              fontWeight: 600,
              color: 'var(--accent-blue)',
              textTransform: 'uppercase',
              letterSpacing: '0.1em',
              marginBottom: '16px',
            }}
          >
            Cases em Destaque
          </div>
          <h2 style={{ fontSize: 'clamp(28px, 4vw, 42px)', fontWeight: 800 }}>
            Tecnologia aplicada a <span className="text-gradient">resultados reais</span>
          </h2>
        </div>

        <ProjectsPresentation
          projects={projects}
          onSelectProject={onSelectProject}
          isVisible={isVisible}
        />
      </div>

      <style>{`
        .project-card .project-overlay {
          position: absolute;
          inset: 0;
          background: rgba(8, 11, 20, 0.85);
          backdrop-filter: blur(4px);
          display: flex;
          align-items: center;
          justify-content: center;
          opacity: 0;
          transition: opacity 0.3s ease;
        }
        .project-card:hover .project-overlay {
          opacity: 1;
        }
      `}</style>
    </section>
  );
};
