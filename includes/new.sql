-- Create modification table
CREATE TABLE modification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mod_no VARCHAR(100),
    directorate VARCHAR(100),
    formation_id INT,
    type_id INT,
    description TEXT,
    recommended_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (formation_id) REFERENCES formation(formation_id),
    FOREIGN KEY (type_id) REFERENCES type(type_id),
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

-- Create rnd table
CREATE TABLE rnd (
    id INT AUTO_INCREMENT PRIMARY KEY,
    directorate VARCHAR(100),
    formation_id INT,
    type_id INT,
    description TEXT,
    rnd_no VARCHAR(100),
    issue_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (formation_id) REFERENCES formation(formation_id),
    FOREIGN KEY (type_id) REFERENCES type(type_id),
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

-- Create indexes for better performance
CREATE INDEX idx_modification_formation ON modification(formation_id);
CREATE INDEX idx_modification_type ON modification(type_id);
CREATE INDEX idx_modification_directorate ON modification(directorate);

CREATE INDEX idx_rnd_formation ON rnd(formation_id);
CREATE INDEX idx_rnd_type ON rnd(type_id);
CREATE INDEX idx_rnd_directorate ON rnd(directorate);