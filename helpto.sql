DROP PROCEDURE IF EXISTS [dbo].[GetBlockChainXML];
GO

CREATE PROCEDURE [dbo].[GetBlockChainXML]
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        (SELECT 
            B.BlockID, 
            B.Timestamp,
            B.PreviousHash, 
            B.Hash,
            (SELECT 
                T.TransactionID, 
                T.SenderID, 
                T.ReceiverID, 
                T.Amount
             FROM PP_DDBB.dbo.Transactions T
             INNER JOIN BlockTransactions BT ON T.TransactionID = BT.TransactionID
             WHERE BT.BlockID = B.BlockID
             FOR XML PATH('Transaction'), TYPE)
         FROM Blocks B
         FOR XML PATH('Block'), ROOT('Blockchain')) AS BlockchainXML;
END;
GO