import React, { useState, useEffect } from 'react';
import { 
  Container, Typography, Box, Paper, Table, TableBody, TableCell, 
  TableContainer, TableHead, TableRow, Button, IconButton, Chip 
} from '@mui/material';
import { Add as AddIcon, Edit as EditIcon, Delete as DeleteIcon } from '@mui/icons-material';
import RuleEditor from './components/RuleEditor';

function App() {
  const [rules, setRules] = useState([]);
  const [editorOpen, setEditorOpen] = useState(false);
  const [editingRule, setEditingRule] = useState(null);

  useEffect(() => {
    fetchRules();
  }, []);

  const fetchRules = async () => {
    try {
      const response = await fetch('../front/api.php?action=get_rules');
      const data = await response.json();
      if (data.rules) {
        setRules(data.rules);
      }
    } catch (error) {
      console.error("Failed to fetch rules", error);
    }
  };

  const handleNew = () => {
    setEditingRule(null);
    setEditorOpen(true);
  };

  const handleEdit = (rule) => {
    setEditingRule(rule);
    setEditorOpen(true);
  };

  const handleDelete = async (id) => {
    if (confirm('Are you sure you want to delete this rule?')) {
      try {
        const body = new URLSearchParams();
        body.append('_glpi_csrf_token', window.glpi_csrf_token || '');
        body.append('id', id);

        await fetch(`../front/api.php?action=delete_rule`, {
          method: 'POST',
          body: body
        });
        fetchRules();
      } catch (error) {
        console.error("Failed to delete rule", error);
      }
    }
  };

  const handleSave = () => {
    fetchRules();
  };

  return (
    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
        <Typography variant="h4" component="h1" gutterBottom>
          Automator Rules
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={handleNew}>
          New Rule
        </Button>
      </Box>

      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell>Name</TableCell>
              <TableCell>Item Type</TableCell>
              <TableCell>Status</TableCell>
              <TableCell>Actions</TableCell>
              <TableCell align="right">Manage</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {rules.map((rule) => (
              <TableRow key={rule.id}>
                <TableCell>{rule.id}</TableCell>
                <TableCell>{rule.name}</TableCell>
                <TableCell>{rule.itemtype}</TableCell>
                <TableCell>
                  <Chip 
                    label={rule.is_active == 1 ? "Active" : "Inactive"} 
                    color={rule.is_active == 1 ? "success" : "default"} 
                    size="small" 
                  />
                </TableCell>
                <TableCell>{rule.actions?.length || 0} actions</TableCell>
                <TableCell align="right">
                  <IconButton color="primary" onClick={() => handleEdit(rule)}>
                    <EditIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => handleDelete(rule.id)}>
                    <DeleteIcon />
                  </IconButton>
                </TableCell>
              </TableRow>
            ))}
            {rules.length === 0 && (
              <TableRow>
                <TableCell colSpan={6} align="center">No rules found.</TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      <RuleEditor 
        open={editorOpen}
        onClose={() => setEditorOpen(false)}
        rule={editingRule}
        onSave={handleSave}
      />
    </Container>
  );
}

export default App;
